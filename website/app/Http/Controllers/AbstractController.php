<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * Class AbstractController
 * @package App\Http\Controllers
 *
 * @OA\Info(
 *     version="1.0",
 *     title="API",
 * )
 *
 * @OA\Server(
 *     description="API V1",
 *     url=API_BASE
 * )
 *
 * @OA\Tag(
 *     name="Questionnaire",
 *     description="Operations about Questionnaires"
 * )
 */
abstract class AbstractController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $resource;
    protected $repository;
    protected $request;
    protected $nbPerPage = 25;

    /**
     * AbstractController constructor.
     */
    public function __construct()
    {
        if (request('per_page')) {
            $this->nbPerPage = request('per_page');
        }
    }

    /**
     * Set the Controller's Resource path
     * @param $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get the Controller's Resource path
     * @return string
     * @throws ModelNotFoundException
     */
    public function getResource()
    {
        if (!$this->resource) {
            $path = explode('\\', get_class($this));
            $this->resource = 'App\Http\Resources\\' . str_replace('Controller', 'Resource', array_pop($path));
        }

        if (!class_exists($this->resource)) {
            throw new ModelNotFoundException("Resource " . $this->resource . " not found");
        }

        return $this->resource;
    }

    /**
     * @return mixed
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return array
     */
    protected function getScopeFilters()
    {
        $scope = [];
        $model = $this->getRepository()->getModel();
        $filters = array_merge($model->getFillable(), $model->getTranslatedAttributes());

        foreach ($filters as $field) {
            if ($this->request->has($field)) {
                $value = $this->request->$field;
                if ($value == 'true') {
                    $value = 1;
                }
                if ($value == 'false') {
                    $value = 0;
                }
                if ($value == 0 || !empty($value)) {
                    $scope[$field] = $value;
                }
            }
        }
        return $scope;
    }

    /**
     * @return array
     */
    private function getOrderFilters()
    {
        $orders = [];
        if ($this->request->has('sort') && $this->request->has('sortOrder')) {
            $orders[$this->request->get('sort')] = $this->request->get('sortOrder');
        } elseif ($this->request->has('sort')) {
            $orders[$this->request->get('sort')] = 'asc';
        }
        return $orders;
    }

    /**
     * Get all includes to set for the Model Collection
     * @return array
     */
    private function parseIncludes()
    {
        return $this->request->get('include') ? explode(',', $this->request->get('include')) : [];
    }

    /**
     * Display a listing of the resource.
     * ex : http://academy.operadeparis.local/api/v1/themes?lang=fr&label=%%lu%%&sort=id&sortOrder=desc
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return $this->getResource()::collection(
            $this->getRepository()->getPaginateCollection(
                $this->nbPerPage,
                $this->getScopeFilters(),
                $this->parseIncludes(),
                $this->getOrderFilters()
            )
        );
    }

    /**
     * Display the specified resource.
     *
     * @param $itemId
     * @return mixed
     */
    public function show($itemId)
    {
        $item = $this->getRepository()->getModel()::with($this->parseIncludes())->find($itemId);
        if (!$item) {
            throw new ModelNotFoundException(
                'call to undefined id [' . $itemId . '] on model [' .
                get_class($this->getRepository()->getModel()) . '}.'
            );
        }

        return $this->getResource()::make($item);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     */
    /*public function store(Request $request)
    {
        $theme = Theme::where('code', $request->theme_code)->firstOrFail();

        if (!$theme) {
            return response()->json([
                'error' => 'Theme not found'
            ], 404);
        }

        $questionnaire = Questionnaire::create([
            'published' => $request->published,
            'level' => $request->level,
            'note_max' => $request->note_max,
            'active:fr' => $request->{'active:fr'},
            'title:fr' => $request->{'title:fr'},
            'description:fr' => $request->{'description:fr'},
            'active:en' => $request->{'active:en'},
            'title:en' => $request->{'title:en'},
            'description:en' => $request->{'description:en'},
        ]);

        $questionnaire->themes()->attach($theme->toArray());

        die();
        //return new (QuestionnaireResource($questionnaire))->response()->setStatusCode(201);
    }*/

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param $itemId
     * @return mixed
     */
    public function update(Request $request, $itemId)
    {
        $this->getRepository()->update($itemId, $request->request->all());
        //$this->getRepository()->getModel()::find($itemId)->update($request->request->all());

        return $this->show($itemId);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $itemId
     * @return \Illuminate\Http\Response
     */
    public function destroy($itemId)
    {
        $item = $this->getRepository()->getModel()::with($this->parseIncludes())->find($itemId);
        if (!$item) {
            throw new ModelNotFoundException(
                'call to undefined id [' . $itemId . '] on model [' .
                get_class($this->getRepository()->getModel()) . '}.'
            );
        }

        $item->delete();

        return response()->json(null, 204);
    }
}
