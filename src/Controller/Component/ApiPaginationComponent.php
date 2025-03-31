<?php
declare(strict_types=1);

namespace BryanCrowe\ApiPagination\Controller\Component;

use Cake\Controller\Component;
use Cake\Datasource\Paging\PaginatedInterface;
use Cake\Event\Event;

/**
 * This is a simple component that injects pagination info into responses when
 * using CakePHP's PaginatorComponent alongside of CakePHP's JsonView or XmlView
 * classes.
 */
class ApiPaginationComponent extends Component
{
    /**
     * Default config.
     *
     * @var array
     */
    protected array $_defaultConfig = [
        'key' => 'pagination',
        'aliases' => [],
        'visible' => [],
    ];

    /**
     * Paging params of paginated result set (if any).
     *
     * @var array
     */
    protected array $pagingParams = [];

    /**
     * Holds the paging information array from the request.
     *
     * @var array
     */
    protected array $pagingInfo = [];

    /**
     * Injects the pagination info into the response if the current request is a
     * JSON or XML request with pagination.
     *
     * @param  \Cake\Event\Event $event The Controller.beforeRender event.
     * @return void
     */
    public function beforeRender(Event $event): void
    {
        if (!$this->isPaginatedApiRequest()) {
            return;
        }

        $subject = $event->getSubject();
        $modelName = ucfirst($this->getConfig('model', $subject->getName()));
        if (isset($this->pagingParams[$modelName])) {
            $this->pagingInfo = $this->pagingParams[$modelName];
        }

        $config = $this->getConfig();

        if (!empty($config['aliases'])) {
            $this->setAliases();
        }

        if (!empty($config['visible'])) {
            $this->setVisibility();
        }

        $subject->set($config['key'], $this->pagingInfo);
        $data = $subject->viewBuilder()->getOption('serialize') ?? [];

        if (is_array($data)) {
            $data[] = $config['key'];
            $subject->viewBuilder()->setOption('serialize', $data);
        }
    }

    /**
     * Aliases the default pagination keys to the new keys that the user defines
     * in the config.
     *
     * @return void
     */
    protected function setAliases(): void
    {
        foreach ($this->getConfig('aliases') as $key => $value) {
            $this->pagingInfo[$value] = $this->pagingInfo[$key];
            unset($this->pagingInfo[$key]);
        }
    }

    /**
     * Removes any pagination keys that haven't been defined as visible in the
     * config.
     *
     * @return void
     */
    protected function setVisibility(): void
    {
        $visible = $this->getConfig('visible');
        foreach ($this->pagingInfo as $key => $value) {
            if (!in_array($key, $visible)) {
                unset($this->pagingInfo[$key]);
            }
        }
    }

    /**
     * Checks whether the current request is a JSON or XML request with
     * pagination.
     *
     * @return bool True if JSON or XML with paging, otherwise false.
     */
    protected function isPaginatedApiRequest(): bool
    {
        if (!$this->getController()->getRequest()->is(['json', 'xml'])) {
            return false;
        }

        // Cake 4 way for the people who want to keep embracing paging attribute pattern
        if ($this->getController()->getRequest()->getAttribute('paging')) {
            $this->pagingParams = $this->getController()->getRequest()->getAttribute('paging');

            return !empty($this->pagingParams);
        }

        // Since cake 5, paging params are no longer part of the request attribute.
        // Hence, we check for all the view vars and if paginated interface found then we pick the first one and use it.
        // @see https://github.com/cakephp/cakephp/pull/16317#issuecomment-1045873277
        foreach ($this->getController()->viewBuilder()->getVars() as $value) {
            if ($value instanceof PaginatedInterface) {
                $this->pagingParams[$value->pagingParam('alias')] = $value->pagingParams();
                break;
            }
        }

        return !empty($this->pagingParams);
    }
}
