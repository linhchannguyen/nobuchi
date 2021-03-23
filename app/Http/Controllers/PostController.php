<?php
namespace App\Http\Controllers;

use App\Http\Requests;
use App\Repositories\PostRepositoryInterface;

class PostController extends Controller
{

    protected $post;

    /**
     * PostController constructor.
     *
     * @param PostRepositoryInterface $post
     */
    public function __construct(PostRepositoryInterface $post)
    {
        $this->post = $post;
    }

    /**
     * List all posts.
     *
     * @return mixed
     */
    public function index()
    {
        $data = [
            'posts' => $this->post->all()
        ];

        return view('templates.posts', $data);
    }

}