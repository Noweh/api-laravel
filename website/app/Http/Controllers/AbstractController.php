<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

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
                if ($value == "true") {
                    $value = 1;
                }
                if ($value == "false") {
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
        if ($this->request->has("sort") && $this->request->has("sortOrder")) {
            $orders[$this->request->get("sort")] = $this->request->get("sortOrder");
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
            $this->getRepository()->getCollection(
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
        return $this->getResource()::make(
            $this->getRepository()->getModel()::with($this->parseIncludes())->find($itemId)
        );
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
        $this->getRepository()->getModel()::find($itemId)->update($request);

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
        $this->getRepository()->getModel()::find($itemId)->delete();

        return response()->json(null, 204);
    }
}
