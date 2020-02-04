<?php

namespace Day4\BelongsToBrowser\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;
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
        $orderby = $request->input('orderby');
        $direction = $request->input('direction') == 'asc' ? 'asc' : 'desc';
        $ck = $request->input('ck');

        if (!Hash::check("$resource:-:$title:-:$image", $ck)) {
            return response('Checksum conflicts with allowed input', 409);
        }

        $query = DB::table($resource)
            ->select('id', "$title as title", "$image as image")
            ->orderBy($orderby, $direction);

        if ($request->has('query') && $search = $request->input('query')) {
            $query->where($title, 'LIKE', '%'.$search.'%');
        }

        if ($request->has('filter') && $filter = $request->input('filter')) {
            foreach ($filter as $field => $value) {
                $query->where($field, '=', $value);
            }
        }

        if ($request->has(['offset', 'limit'])) {
            $offset = $request->input('offset');
            $limit = $request->input('limit');

            $query->offset($offset)
                ->limit($limit);
        }

        return response()->json($query->get());
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

        $query = DB::table($resource)
            ->select('id', "$title as title", "$image as image")
            ->whereIn('id', explode(',', $ids));

        return response()->json($query->get());
    }
}
