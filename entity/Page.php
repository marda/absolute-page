<?php


namespace Absolute\Module\Page\Entity;

use Absolute\Core\Entity\BaseEntity;

class Page extends BaseEntity 
{

  private $id;
  private $name;
  private $type;
  private $content;
  private $url;
  private $display;
  private $created;
  private $modified;
  private $starred;

  private $users = [];
  private $teams = [];
  private $categories = [];
  private $thumb = null;

	public function __construct($id, $name, $type, $url, $content, $display, $starred, $created, $modified) 
  {
    $this->id = $id;
    $this->name = $name;
    $this->content = $content;
    $this->url = $url;
    $this->type = $type;
    $this->display = ($display) ? true : false;
    $this->created = $created;
    $this->modified = $modified;
    $this->starred = ($starred) ? true : false;
	}

  public function getId() 
  {
    return $this->id;
  }

  public function getContent() 
  {
    return $this->content;
  }

  public function getDisplay() 
  {
    return $this->display;
  }

  public function getName() 
  {
    return $this->name;
  }

  public function getType() 
  {
    return $this->type;
  }

  public function getUrl() 
  {
    return $this->url;
  }

  public function getStarred() 
  {
    return $this->starred;
  }

  public function getRecent() 
  {
    $date = new \DateTime();
    $date->modify('-1 week');
    return ($this->modified > $date) ? true : false;
  }

  public function getCreated() 
  {
    return $this->created;
  }

  public function getModified() 
  {
    return $this->modified;
  }

  public function getThumb() 
  {
    return $this->thumb;
  }

  public function getUsers() 
  {
    return $this->users;
  }
  
  public function getTeams()
  {
    return $this->teams;
  }

  public function getCategories()
  {
    return $this->categories;
  }

  // SETTERS

  public function setThumb($thumb) 
  {
    $this->thumb = $thumb;
  }

  // ADDERS

  public function addUser($user) 
  {
    $this->users[$user->getId()] = $user;
  }

  public function addTeam($team) 
  {
    $this->teams[$team->getId()] = $team;
  }

  public function addCategory($category) 
  {
    $this->categories[$category->getId()] = $category;
  }

  // OTHER METHODS

  public function toJsonString() 
  {
    return json_encode(array(
      "id" => $this->id,
      "name" => $this->name,
      "display" => $this->display,
      "starred" => $this->starred,
      "content" => $this->content,
      "url" => $this->url,
      "type" => $this->type,
      "created" => $this->created->format("F j, Y"),
      "modified" => $this->created->format("F j, Y"),
      "users" => array_values(array_map(function($user) { return $user->toJson(); }, $this->users)),
      "teams" => array_values(array_map(function($team) { return $team->toJson(); }, $this->teams)),
      "categories" => array_values(array_map(function($category) { return $category->toJson(); }, $this->categories)), 
    ));
  }

  public function toJson() 
  {
    return array(
      "id" => $this->id,
      "name" => $this->name,
      "display" => $this->display,
      "starred" => $this->starred,
      "created" => $this->created->format("F j, Y"),
      "modified" => $this->created->format("F j, Y"),
      "users" => array_map(function($user) { return $user->toJson(); }, $this->users),
      "teams" => array_map(function($team) { return $team->toJson(); }, $this->teams),
      "categories" => array_map(function($category) { return $category->toJson(); }, $this->categories), 
    );
  }
}

