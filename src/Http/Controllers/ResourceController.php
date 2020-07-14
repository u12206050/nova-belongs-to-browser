<?php

namespace Day4\BelongsToBrowser\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ResourceController extends Controller
{
    /**
     * List the resources for administration.
     *
     * @param  NovaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function load(NovaRequest $request, $resource)
    {
        $title = $request->input('title');
        $image = $request->input('image');
        $orderby = empty($request->input('orderby')) ? 'updated_at' : $request->input('orderby');
        $direction = $request->input('direction') == 'asc' ? 'asc' : 'desc';
        $ck = $request->input('ck');

        if (!Hash::check("$resource:-:$title:-:$image", $ck)) {
            return response('Checksum conflicts with allowed input', 409);
        }

        $resourceClass = Nova::resourceForKey($resource);
        if (!$resourceClass) {
            abort("Missing resource class");
        }

        $modelClass = $resourceClass::$model;
        $m = new $modelClass();

        $baseWhere = [];
        $translationWhere = [];

        // Test for translation field
        if (!$m->useTranslationFallback) {
            array_push($translationWhere, ['locale', '=', app()->getLocale()]);
        }

        if ($request->has('query') && $search = $request->input('query')) {
            if (isset($m->translatedAttributes) && in_array($title, $m->translatedAttributes)) {
                array_push($translationWhere, [$title, 'LIKE', "%$search%"]);
            } else {
                array_push($baseWhere, [$title, 'LIKE', "%$search%"]);
            }
        }

        if ($request->has('filter') && $filter = $request->input('filter')) {
            foreach ($filter as $field => $value) {
                if (isset($m->translatedAttributes) && in_array($field, $m->translatedAttributes)) {
                    array_push($translationWhere, [$field, 'LIKE', "%$value%"]);
                } else {
                    array_push($baseWhere, [$field, 'LIKE', "%$value%"]);
                }
            }
        }

        $query = $modelClass::orderBy($orderby, $direction);
        if (isset($m->translatedAttributes)) {
            if (count($translationWhere)) {
                $query->whereHas('translations', function($q) use ($m, $translationWhere) {
                    $q->where($translationWhere);
                });
            }
        }

        if (count($baseWhere)) {
            $query->where($baseWhere);
        }

        if ($request->has(['offset', 'limit'])) {
            $offset = $request->input('offset');
            $limit = $request->input('limit');

            $query->offset($offset)
                ->limit($limit);
        }

        $results = array_map(function($row) use ($title, $image) {
            return [
                'id' => $row['id'],
                'title' => $row[$title],
                'image' => $row[$image]
            ];
        }, $query->get()->toArray());

        return response()->json($results);
    }


    /**
     * Fetch the resources for the specified ids
     *
     * @param  NovaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function fetch(NovaRequest $request, $resource)
    {
        $ids = $request->input('ids');
        $title = $request->input('title');
        $image = $request->input('image');
        $ck = $request->input('ck');

        if (!Hash::check("$resource:-:$title:-:$image", $ck)) {
            return response('Checksum conflicts with allowed input', 409);
        }

        $resourceClass = Nova::resourceForKey($resource);
        if (!$resourceClass) {
            abort("Missing resource class");
        }

        $modelClass = $resourceClass::$model;
        $query = $modelClass::find(explode(',', $ids));

        $results = array_map(function($row) use ($title, $image) {
            return [
                'id' => $row['id'],
                'title' => $row[$title],
                'image' => $row[$image]
            ];
        }, $query->toArray());

        return response()->json($results);

        /*
        $query = DB::table($resource)
            ->select('id', "$title as title", "$image as image")
            ->whereIn('id', explode(',', $ids));

        return response()->json($query->get());
        */
    }
}
