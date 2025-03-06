<?php
declare(strict_types=1);

namespace BryanCrowe\ApiPagination\Test\TestCase\Controller\Component;

use BryanCrowe\ApiPagination\Controller\Component\ApiPaginationComponent;
use BryanCrowe\ApiPagination\TestApp\Controller\ArticlesIndexController;
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
 * @property ArticlesIndexController $controller
 */
class ApiPaginationComponentOnNonConventionalControllerNameTest extends TestCase
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
        $this->controller = new ArticlesIndexController($this->request);
        $this->controller = $this->controller->setResponse($this->response);
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
     * Test that a non conventional controller name is supported using the 'model' config.
     *
     * @dataProvider dataForTestVariousModelValueOnNonConventionalController
     * @param array $config
     * @param $expected
     * @return void
     */
    public function testVariousModelValueOnNonConventionalController(array $config, $expected)
    {
        $this->controller->setRequest(
            $this->controller->getRequest()->withEnv('HTTP_ACCEPT', 'application/json')
        );
        $this->controller->set('data', $this->controller->paginate($this->Articles));
        $apiPaginationComponent = new ApiPaginationComponent($this->controller->components(), $config);
        $event = new Event('Controller.beforeRender', $this->controller);
        $apiPaginationComponent->beforeRender($event);

        $result = $apiPaginationComponent->getController()->viewBuilder()->getVar('pagination');
        $this->assertSame($expected, $result);
    }

    /**
     * If the name of the paginated model is not specified, the result of the pagination
     * on a controller not having the same name as the model fails.
     *
     * @return array[]
     */
    public static function dataForTestVariousModelValueOnNonConventionalController(): array
    {
        return [
            [[], []],
            [['model' => 'Articles'], self::getDefaultPagination()],
            [['model' => 'articles'], self::getDefaultPagination()],
            [['model' => 'NonExistingModel'], []],
        ];
    }

    /**
     * Returns the standard pagination result.
     *
     * @return array
     */
    private static function getDefaultPagination(): array
    {
        return [
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
    }
}
