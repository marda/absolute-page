<?php

namespace Absolute\Module\Page\Presenter;

use Nette\Http\Response;
use Nette\Application\Responses\JsonResponse;
use Absolute\Module\Page\Presenter\PageBasePresenter;

class DefaultPresenter extends PageBasePresenter
{

    /** @var \Absolute\Module\Page\Manager\PageCRUDManager @inject */
    public $pageCRUDManager;

    /** @var \Absolute\Module\Page\Manager\PageManager @inject */
    public $pageManager;

    public function startup()
    {
        parent::startup();
    }

    public function renderDefault($resourceId)
    {
        switch ($this->httpRequest->getMethod())
        {
            case 'GET':
                if ($resourceId != null)
                    $this->_getRequest($resourceId);
                else
                    $this->_getListRequest();
                break;
            case 'POST':
                $this->_postRequest($resourceId);
                break;
            case 'PUT':
                $this->_putRequest($resourceId);
                break;
            case 'DELETE':
                $this->_deleteRequest($resourceId);
            default:

                break;
        }
        $this->sendResponse(new JsonResponse(
                $this->jsonResponse->toJson(), "application/json;charset=utf-8"
        ));
    }

    private function _getRequest($id)
    {
        $page = $this->pageManager->getById($id);
        if (!$page)
        {
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
            return;
        }
        $this->jsonResponse->payload = $page->toJson();
        $this->httpResponse->setCode(Response::S200_OK);
    }

    private function _getListRequest()
    {
        $pages = $this->pageManager->getList();
        $this->httpResponse->setCode(Response::S200_OK);

        $this->jsonResponse->payload = array_map(function($n)
        {
            return $n->toJson();
        }, $pages);
    }

    private function _putRequest($id)
    {
        $post = json_decode($this->httpRequest->getRawBody(), true);
        $this->jsonResponse->payload = [];
        $ret = $this->pageCRUDManager->update($id, $post);
        if (!$ret)
        {
            $this->httpResponse->setCode(Response::S500_INTERNAL_SERVER_ERROR);
        }
        else
        {
            $this->httpResponse->setCode(Response::S201_CREATED);
        }
    }

    private function _postRequest($urlId)
    {
        $post = json_decode($this->httpRequest->getRawBody());
        $ret = $this->pageCRUDManager->create($post->name, $post->type, $post->url, $post->content);
        if (!$ret)
        {
            $this->httpResponse->setCode(Response::S500_INTERNAL_SERVER_ERROR);
        }
        else
        {
            $this->httpResponse->setCode(Response::S201_CREATED);
        }
    }

    private function _deleteRequest($id)
    {
        $this->pageCRUDManager->delete($id);
        $this->httpResponse->setCode(Response::S200_OK);
    }

}
