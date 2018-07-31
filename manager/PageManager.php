<?php

namespace Absolute\Module\Page\Manager;

use Absolute\Module\File\Manager\FileManager;
use Absolute\Module\Category\Manager\CategoryManager;
use Absolute\Module\User\Manager\UserManager;
use Absolute\Module\Team\Manager\TeamManager;
use Absolute\Core\Manager\BaseManager;
use Absolute\Module\Page\Entity\Page;
use Nette\Database\Context;

class PageManager extends BaseManager
{

    private $teamManager, $fileManager,$categoryManager, $userManager;

    public function __construct(Context $database, FileManager $fileManager, CategoryManager $categoryManager, UserManager $userManager, TeamManager $teamManager)
    {
        parent::__construct($database);
        $this->fileManager = $fileManager;
        $this->categoryManager = $categoryManager;
        $this->userManager = $userManager;
        $this->teamManager = $teamManager;
    }

    public function getPage($db)
    {
        if ($db == false)
        {
            return false;
        }
        $object = new Page($db->id, $db->name, $db->type, $db->url, $db->content, $db->display, $db->starred, $db->created, $db->modified);
        foreach ($db->related('page_user') as $userDb)
        {
            $user = $this->userManager->getUser($userDb->user);
            if ($user)
            {
                $object->addUser($user);
            }
        }
        foreach ($db->related('page_team') as $teamDb)
        {
            $team = $this->teamManager->getTeam($teamDb->team);
            if ($team)
            {
                $object->addTeam($team);
            }
        }
        foreach ($db->related('page_category') as $categoryDb)
        {
            $category = $this->categoryManager->getCategory($categoryDb->category);
            if ($category)
            {
                $object->addCategory($category);
            }
        }
        return $object;
    }

    /* INTERNAL METHODS */

    /* INTERNAL/EXTERNAL INTERFACE */

    public function _getById($id)
    {
        $resultDb = $this->database->table('page')->get($id);
        return $this->getPage($resultDb);
    }

    private function _getList()
    {
        $ret = array();
        $resultDb = $this->database->table('page')->where('id NOT IN (SELECT page_id FROM project_page)')->order('name');
        foreach ($resultDb as $db)
        {
            $object = $this->getPage($db);
            $ret[] = $object;
        }
        return $ret;
    }

    private function _getProjectList($projectId)
    {
        $ret = array();
        $resultDb = $this->database->table('page')->where(':project_page.project_id', $projectId)->order('name');
        foreach ($resultDb as $db)
        {
            $object = $this->getPage($db);
            $ret[] = $object;
        }
        return $ret;
    }

    private function _getProjectVisibleList($projectId)
    {
        $ret = array();
        $resultDb = $this->database->table('page')->where('display', true)->where(':project_page.project_id', $projectId)->order('name');
        foreach ($resultDb as $db)
        {
            $object = $this->getPage($db);
            $ret[] = $object;
        }
        return $ret;
    }

    private function _getUserProjectList($userId)
    {
        $projects = $this->database->table('project_user')->where('user_id', $userId)->fetchPairs('project_id', 'role');
        $ret = [];
        $resultDb = $this->database->table('page')->where(':project_page.project_id', array_keys($projects))->order('created DESC');
        foreach ($resultDb as $db)
        {
            $object = $this->getPage($db);
            if ($db->file)
            {
                $object->setThumb($this->_getFile($db->file));
            }
            $ret[] = $object;
        }
        return $ret;
    }

    private function _getListDisplayed()
    {
        $ret = array();
        $resultDb = $this->database->table('page')->where('display', true)->order('name');
        foreach ($resultDb as $db)
        {
            $object = $this->getPage($db);
            $ret[] = $object;
        }
        return $ret;
    }

    private function _canUserEdit($id, $userId)
    {
        $db = $this->database->table('page')->get($id);
        if (!$db)
        {
            return false;
        }
        $projectsInManagement = $this->database->table('project_user')->where('user_id', $userId)->where('role', array('owner', 'manager'))->fetchPairs('project_id', 'project_id');
        $projects = $this->database->table('project_page')->where('page_id', $id)->fetchPairs('project_id', 'project_id');
        return (!empty(array_intersect($projects, $projectsInManagement))) ? true : false;
    }

    private function _canUserView($id, $userId)
    {
        $db = $this->database->table('page')->get($id);
        if (!$db)
        {
            return false;
        }
        // Can view based on page assigned to project
        $projectsInManagement = $this->database->query("SELECT project_id AS id FROM project_user WHERE user_id = ? UNION SELECT project_id AS id FROM project_team JOIN team_user WHERE user_id = ? UNION SELECT project_id AS id FROM project_category JOIN category_user WHERE user_id = ?", $userId, $userId, $userId)->fetchPairs('id', 'id');
        $projects = $this->database->table('project_page')->where('page_id', $id)->fetchPairs('project_id', 'project_id');
        if (empty(array_intersect($projects, $projectsInManagement)) && !empty($projects))
        {
            return false;
        }
        // Can view based on page assigned to users, teams or categories
        $db = $this->database->query("SELECT page_id AS id FROM page_user WHERE user_id = ? UNION SELECT page_id AS id FROM page_team JOIN team_user WHERE user_id = ? UNION SELECT page_id AS id FROM page_category JOIN category_user WHERE user_id = ? UNION SELECT id FROM page WHERE id NOT IN (SELECT page_id FROM page_user) AND id NOT IN (SELECT page_id FROM page_team) AND id NOT IN (SELECT page_id FROM page_category)", $userId, $userId, $userId);
        $result = $db->fetchPairs("id", "id");
        if (array_key_exists($id, $result))
        {
            return true;
        }
        return false;
    }

    /* EXTERNAL METHOD */

    public function getById($id)
    {
        return $this->_getById($id);
    }

    public function getList()
    {
        return $this->_getList();
    }

    public function getProjectList($projectId)
    {
        return $this->_getProjectList($projectId);
    }

    public function getProjectVisibleList($projectId)
    {
        return $this->_getProjectVisibleList($projectId);
    }

    public function getListDisplayed()
    {
        return $this->_getListDisplayed();
    }

    public function getUserProjectList($userId)
    {
        return $this->_getUserProjectList($userId);
    }

    public function canUserEdit($id, $userId)
    {
        return $this->_canUserEdit($id, $userId);
    }

    public function canUserView($id, $userId)
    {
        return $this->_canUserView($id, $userId);
    }

}
