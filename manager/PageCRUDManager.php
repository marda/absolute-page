<?php

namespace Absolute\Module\Page\Manager;

use Absolute\Core\Manager\BaseCRUDManager;
use Nette\Database\Context;

class PageCRUDManager extends BaseCRUDManager
{

    public function __construct(Context $database)
    {
        parent::__construct($database);
    }

    // OTHER METHODS
    // CONNECT METHODS

    public function connectUsers($id, $users)
    {
        $users = array_unique(array_filter($users));
        // DELETE
        $this->database->table('page_user')->where('page_id', $id)->delete();
        // INSERT NEW
        $data = [];
        foreach ($users as $userId)
        {
            $data[] = array(
                "page_id" => $id,
                "user_id" => $userId,
            );
        }

        if (!empty($data))
        {
            $this->database->table('page_user')->insert($data);
        }
        return true;
    }

    public function connectTeams($id, $teams)
    {
        $teams = array_unique(array_filter($teams));
        // DELETE
        $this->database->table('page_team')->where('page_id', $id)->delete();
        // INSERT NEW
        $data = [];
        foreach ($teams as $team)
        {
            $data[] = [
                "team_id" => $team,
                "page_id" => $id,
            ];
        }
        if (!empty($data))
        {
            $this->database->table("page_team")->insert($data);
        }
        return true;
    }

    public function connectCategories($id, $categories)
    {
        $categories = array_unique(array_filter($categories));
        // DELETE
        $this->database->table('page_category')->where('page_id', $id)->delete();
        // INSERT NEW
        $data = [];
        foreach ($categories as $category)
        {
            $data[] = [
                "category_id" => $category,
                "page_id" => $id,
            ];
        }
        if (!empty($data))
        {
            $this->database->table("page_category")->insert($data);
        }
        return true;
    }

    public function connectProjects($id, $projects)
    {
        $projects = array_unique(array_filter($projects));
        // DELETE
        $this->database->table('project_page')->where('page_id', $id)->delete();
        // INSERT NEW
        $data = [];
        foreach ($projects as $project)
        {
            $data[] = [
                "project_id" => $project,
                "page_id" => $id,
            ];
        }
        if (!empty($data))
        {
            $this->database->table("project_page")->insert($data);
        }
        return true;
    }

    public function connectProject($id, $projectId)
    {
        $this->database->table('project_page')->where('page_id', $id)->delete();
        return $this->database->table('project_page')->insert(array(
                    "page_id" => $id,
                    "project_id" => $projectId
        ));
    }

    // CUD METHODS

    /*public function addStar($id)
    {
        return $this->database->table('page')->where('id', $id)->update(array('starred' => true));
    }

    public function removeStar($id)
    {
        return $this->database->table('page')->where('id', $id)->update(array('starred' => false));
    }

    public function show($id)
    {
        return $this->database->table('page')->where('id', $id)->update(array('display' => true));
    }

    public function hide($id)
    {
        return $this->database->table('page')->where('id', $id)->update(array('display' => false));
    }*/

    public function create($name, $type, $url, $content)
    {
        return $this->database->table('page')->insert(array(
                    'name' => $name,
                    'content' => $content,
                    'type' => $type,
                    'url' => $url,
                    'created' => new \DateTime(),
                    'modified' => new \DateTime(),
                    'display' => true,
        ));
    }

    public function delete($id)
    {
        $this->database->table('project_page')->where('page_id', $id)->delete();
        $this->database->table('page_category')->where('page_id', $id)->delete();
        $this->database->table('page_team')->where('page_id', $id)->delete();
        $this->database->table('page_user')->where('page_id', $id)->delete();
        return $this->database->table('page')->where('id', $id)->delete();
    }

    public function update($id, $array)
    {
        if(isset($array['users']))
            $this->connectUsers ( $id, $array['users']);
        if(isset($array['teams']))
            $this->connectTeams ( $id, $array['teams']);
        if(isset($array['categories']))
            $this->connectCategories ( $id, $array['categories']);
        if(isset($array['projects']))
            $this->connectProjects( $id, $array['projects']);
        
        unset($array['id']);
        unset($array['created']);
        unset($array['users']);
        unset($array['teams']);
        unset($array['categories']);
        unset($array['projects']);
        
        $array['modified'] = new \DateTime();
        return $this->database->table('page')->where('id', $id)->update($array);
    }

}
