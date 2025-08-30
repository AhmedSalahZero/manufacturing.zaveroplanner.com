<?php

namespace App\Http\Middleware;

use Closure;
use Hamcrest\Type\IsInteger;
use Illuminate\Support\Facades\Auth;

class UserProjects
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $project_id = intval($request->segment(2)) != 0 ? intval($request->segment(2)) : intval($request->segment(3));

        $founded_project = Auth::user()->projects()->where('id',$project_id)->first();
        if (!isset($founded_project) && $project_id != 0) {
            return abort(403);
        }

        return $next($request);

    }
}
