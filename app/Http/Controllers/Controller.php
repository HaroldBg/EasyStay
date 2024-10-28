<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="EasyStay Documentation",
 *     description="Ceci est la documentation de EasyStay.",
 *     @OA\Contact(
 *         email="haroldavademe0@gmail.com"
 *     )
 * )
 */
class Controller extends BaseController
{

    use AuthorizesRequests, ValidatesRequests;
}
