<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Services\DataForSeoService;

class SerpController extends Controller
{
    public function index(DataForSeoService $dfs)
    {
        $locations = $dfs->getDefaultLocationsMap();
        $languages = $dfs->getDefaultLanguagesMap();

        return view('serp.index', compact('locations', 'languages'));
    }

    public function search(Request $request, DataForSeoService $dfs)
    {
        $locations = array_keys($dfs->getDefaultLocationsMap());
        $languages = array_keys($dfs->getDefaultLanguagesMap());

        try {
            $data = $request->validate([
                'keyword'       => ['required', 'string', 'min:2', 'max:200'],
                'domain'        => [
                    'required',
                    'string',
                    'max:200',
                    function ($attr, $val, $fail) {
                        $v = strtolower(trim($val));
                        if (preg_match('~^https?://~', $v)) {
                            return $fail('Вкажіть лише домен без http/https.');
                        }
                        if (!preg_match('~^[a-z0-9.-]+\.[a-z]{2,}$~i', $v)) {
                            return $fail('Невалідний домен.');
                        }
                        if (strpos($v, ' ') !== false) {
                            return $fail('Домен не може містити пробіли.');
                        }
                    }
                ],
                'location_code' => ['required', 'integer', Rule::in($locations)],
                'language_code' => ['required', 'string', Rule::in($languages)],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $e->errors()
            ], 422);
        }

        $data['domain'] = preg_replace('~^https?://~i', '', trim($data['domain']));
        $data['domain'] = rtrim($data['domain'], '/');

        $res = $dfs->getWebsitePosition(
            $data['keyword'],
            $data['domain'],
            (int) $data['location_code'],
            $data['language_code']
        );

        if (!($res['success'] ?? false)) {
            $status = (int) ($res['status_code'] ?? 502);
            if ($status < 400 || $status > 599) $status = 502;
            return response()->json($res, $status);
        }

        return response()->json($res, 200);
    }
}
