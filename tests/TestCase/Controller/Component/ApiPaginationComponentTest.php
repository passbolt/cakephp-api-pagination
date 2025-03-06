<?php
declare(strict_types=1);

namespace BryanCrowe\ApiPagination\Test\TestCase\Controller\Component;

use BryanCrowe\ApiPagination\Controller\Component\ApiPaginationComponent;
use BryanCrowe\ApiPagination\TestApp\Controller\ArticlesController;
use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest as Request;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * ApiPaginationComponentTest class
 *
 * @property ArticlesController $controller
 */
class ApiPaginationComponentTest extends TestCase
{
    public array $fixtures = ['plugin.BryanCrowe/ApiPagination.Articles'];

    protected ?Request $request = null;

    protected ?Response $response = null;

    protected ?Controller $controller = null;

    protected ?Table $Articles = null;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->request = new Request(['url' => '/articles']);
        $this->response = $this->createMock('Cake\Http\Response');
        $this->controller = new ArticlesController($this->request);
        $this->controller->setResponse($this->response);
        $this->Articles = TableRegistry::getTableLocator()->get('BryanCrowe/ApiPagination.Articles', ['table' => 'bryancrowe_articles']);
        parent::setUp();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test that a non API or paginated request returns null.
     *
     * @return void
     */
    public function testNonApiPaginatedRequest()
    {
        $apiPaginationComponent = new ApiPaginationComponent($this->controller->components());
        $event = new Event('Controller.beforeRender', $this->controller);

        $this->assertNull($apiPaginationComponent->beforeRender($event));
    }

    /**
     * Test the expected pagination information for the component's default
     * config.
     *
     * @return void
     */
    public function testDefaultPaginationSettings()
    {
        $this->controller->setRequest(
            $this->controller->getRequest()->withEnv('HTTP_ACCEPT', 'application/json')
        );
        $this->controller->set('data', $this->controller->paginate($this->Articles));
        $apiPaginationComponent = new ApiPaginationComponent($this->controller->components());
        $event = new Event('Controller.beforeRender', $this->controller);
        $apiPaginationComponent->beforeRender($event);

        $result = $apiPaginationComponent->getController()->viewBuilder()->getVar('pagination');
        $expected = [
            'sort' => null,
            'direction' => null,
            'sortDefault' => false,
            'directionDefault' => false,
            'completeSort' => [],
            'perPage' => 20,
            'requestedPage' => 1,
            'alias' => 'Articles',
            'scope' => null,
            'limit' => null,
            'count' => 20,
            'totalCount' => 23,
            'pageCount' => 2,
            'currentPage' => 1,
            'start' => 1,
            'end' => 20,
            'hasPrevPage' => false,
            'hasNextPage' => true,
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test that visibility-only correctly sets the visible keys.
     *
     * @return void
     */
    public function testVisibilitySettings()
    {
        $this->controller->setRequest(
            $this->controller->getRequest()->withEnv('HTTP_ACCEPT', 'application/json')
        );
        $this->controller->set('data', $this->controller->paginate($this->Articles));
        $apiPaginationComponent = new ApiPaginationComponent(
            $this->controller->components(),
            [
                'visible' => [
                    'requestedPage',
                    'count',
                    'totalCount',
                    'hasPrevPage',
                    'hasNextPage',
                    'pageCount',
                ],
            ]
        );
        $event = new Event('Controller.beforeRender', $this->controller);
        $apiPaginationComponent->beforeRender($event);

        $result = $apiPaginationComponent->getController()->viewBuilder()->getVar('pagination');
        $expected = [
            'requestedPage' => 1,
            'count' => 20,
            'totalCount' => 23,
            'pageCount' => 2,
            'hasPrevPage' => false,
            'hasNextPage' => true,
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test that alias-only correctly sets aliases the keys.
     *
     * @return void
     */
    public function testAliasSettings()
    {
        $this->controller->setRequest(
            $this->controller->getRequest()->withEnv('HTTP_ACCEPT', 'application/json')
        );
        $this->controller->set('data', $this->controller->paginate($this->Articles));
        $apiPaginationComponent = new ApiPaginationComponent(
            $this->controller->components(),
            [
                'aliases' => [
                    'currentPage' => 'curPage',
                    'perPage' => 'currentCount',
                    'totalCount' => 'noOfResults',
                ],
            ]
        );
        $event = new Event('Controller.beforeRender', $this->controller);
        $apiPaginationComponent->beforeRender($event);

        $result = $apiPaginationComponent->getController()->viewBuilder()->getVar('pagination');
        $expected = [
            'sort' => null,
            'direction' => null,
            'sortDefault' => false,
            'directionDefault' => false,
            'completeSort' => [],
            'requestedPage' => 1,
            'alias' => 'Articles',
            'scope' => null,
            'limit' => null,
            'count' => 20,
            'pageCount' => 2,
            'start' => 1,
            'end' => 20,
            'hasPrevPage' => false,
            'hasNextPage' => true,
            'curPage' => 1,
            'currentCount' => 20,
            'noOfResults' => 23,
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test that key-only correctly sets the pagination key.
     *
     * @return void
     */
    public function testKeySetting()
    {
        $this->controller->setRequest(
            $this->controller->getRequest()->withEnv('HTTP_ACCEPT', 'application/json')
        );
        $this->controller->set('data', $this->controller->paginate($this->Articles));
        $apiPaginationComponent = new ApiPaginationComponent(
            $this->controller->components(),
            ['key' => 'paging']
        );
        $event = new Event('Controller.beforeRender', $this->controller);
        $apiPaginationComponent->beforeRender($event);

        $result = $apiPaginationComponent->getController()->viewBuilder()->getVar('paging');
        $expected = [
            'sort' => null,
            'direction' => null,
            'sortDefault' => false,
            'directionDefault' => false,
            'completeSort' => [],
            'perPage' => 20,
            'requestedPage' => 1,
            'alias' => 'Articles',
            'scope' => null,
            'limit' => null,
            'count' => 20,
            'totalCount' => 23,
            'pageCount' => 2,
            'currentPage' => 1,
            'start' => 1,
            'end' => 20,
            'hasPrevPage' => false,
            'hasNextPage' => true,
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test that all settings being used together work correctly.
     *
     * @return void
     */
    public function testAllSettings()
    {
        $this->controller->setRequest(
            $this->controller->getRequest()->withEnv('HTTP_ACCEPT', 'application/json')
        );
        $this->controller->set('data', $this->controller->paginate($this->Articles));
        $apiPaginationComponent = new ApiPaginationComponent(
            $this->controller->components(),
            [
                'key' => 'fun',
                'aliases' => [
                    'currentPage' => 'page',
                    'totalCount' => 'noOfResults',
                    'limit' => 'unusedAlias',
                ],
                'visible' => [
                    'page',
                    'noOfResults',
                    'limit',
                    'hasPrevPage',
                    'hasNextPage',
                ],
            ]
        );
        $event = new Event('Controller.beforeRender', $this->controller);
        $apiPaginationComponent->beforeRender($event);

        $result = $apiPaginationComponent->getController()->viewBuilder()->getVar('fun');
        $expected = [
            'hasPrevPage' => false,
            'hasNextPage' => true,
            'page' => 1,
            'noOfResults' => 23,
        ];

        $this->assertSame($expected, $result);
    }
}
