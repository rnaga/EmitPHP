<?

namespace EmitTest;

use PHPUnit\Framework\TestCase;
use Emit\Router\PathMatcher;
use Emit\Router\Route;

class RouteTest extends TestCase
{
    protected $route;
    protected $subRoute;

    protected function setUp()
    {
        $this->route = new Route();
        $this->subRoute = new Route();
    }

    public function testInstance()
    {
        $this->assertInstanceOf(Route::class, $this->route);
        $this->assertInstanceOf(Route::class, $this->subRoute);
    }

    /**
        @depends testInstance
    */
    public function testUseFunction()
    {

        $route = $this->route;

        // Register function
        $route->use(function($params){
            // Pass
        });

        // With path
        $route->use("/path/to", function($params){
            // Pass
        });

        $subRoute = $this->subRoute;

        $subRoute->use(function($params){
  
        });

        // Add subRoute
        $route->use($subRoute);

        // PathMatcher
        $matcher = PathMatcher::regex("a/[0-9]/[a-b]/");

        $route->use($matcher, function($params){
            // Pass
        });
    } 

    /**
        @depends testUseFunction
    */
    public function testDispatch()
    {
        $route = $this->route;
        $params = [];
        $route->dispatch("/", $matches, $params);
    }

    /**
        @depends testInstance
        @expectedException \Emit\EmitException
    */
    public function testUseEmptyArgument()
    {
        $route = $this->route;
        $route->use();
    }

    /**
        @depends testInstance
        @expectedException \Emit\EmitException
    */
    public function testUseInvalidHandler()
    {
        $route = $this->route;
        $route->use("/path/to", new \stdClass());
    }

    // Test for PatchMatcher

    public function testPatchMatcher()
    { 
        $matcher = PathMatcher::compile("/");
        $this->assertInstanceOf(PathMatcher::class, $matcher);

        // Checking for cleaning up slash
        $matcher = PathMatcher::compile("////a/////b///////////c");
        $this->assertInstanceOf(PathMatcher::class, $matcher);

        $r = $matcher->matches("a/b/c", $matches);
        $this->assertEquals(true, $r); 

        $matcher = PathMatcher::compile('/:id');
        $this->assertInstanceOf(PathMatcher::class, $matcher);

        $r = $matcher->matches("/1234", $matches);
        $this->assertEquals(true, $r);

        $matcher = PathMatcher::compile(['/:id/:name', ['id' => '[0-9]+', 'name' => '[a-z]+']]);
        $this->assertInstanceOf(PathMatcher::class, $matcher);

        $r = $matcher->matches("/1234/name", $matches);
        $this->assertEquals(true, $r);

        $matcher = PathMatcher::regex('a/b/(.*)');
        $this->assertInstanceOf(PathMatcher::class, $matcher);

        $r = $matcher->matches("a/b/c", $matches);
        $this->assertEquals(true, $r);
    }

    /**
        @depends testInstance
        @expectedException \Emit\EmitException
    */
    public function testPatchMatcherInvalidRegex()
    {
        $matcher = PathMatcher::regex('a/b/(.*)////[]');
    }

    /**
        @depends testInstance
        @expectedException \Emit\EmitException
    */
    public function testPathMatcherInvalidArguments1()
    {
        // Doesn't allow integer
        $matcher = PathMatcher::compile(1234);
    }

    /**
        @depends testInstance
        @expectedException \Emit\EmitException
    */
    public function testPathMatcherInvalidArguments2()
    {
        $matcher = PathMatcher::compile([]);
    }

    /**
        @depends testInstance
        @expectedException \Emit\EmitException
    */
    public function testPathMatcherInvalidArguments3()
    {
        // Check for $varMappingRegex  = "/^:([a-zA-Z]+(?:[a-zA-Z0-9_]*))$/";
        $matcher = PathMatcher::compile('/:{]ksdsds');
    }

    /**
        @depends testInstance
        @expectedException \Emit\EmitException
    */
    public function testPathMatcherInvalidArguments4()
    {
        // Check for $varMappingRegex  = "/^:([a-zA-Z]+(?:[a-zA-Z0-9_]*))$/";
        $matcher = PathMatcher::compile('/a/[a-z]/(.*)/');
    }

}

