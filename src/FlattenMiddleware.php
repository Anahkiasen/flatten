<?php
namespace Flatten;

use Closure;
use Illuminate\Contracts\Routing\Middleware;

class FlattenMiddleware implements Middleware
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var EventHandler
     */
    private $events;

    /**
     * @param Context      $context
     * @param EventHandler $events
     */
    public function __construct(Context $context, EventHandler $events)
    {
        $this->context = $context;
        $this->events = $events;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Cancel if Flatten shouldn't run here
        if (!$this->context->shouldRun()) {
            return $next($request);
        }

        // Launch startup event
        $this->events->onApplicationBoot();

        return $next($request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     */
    public function terminate($request, $response)
    {
        $this->events->onApplicationDone($response);
    }
}

