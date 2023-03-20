<?php

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Facades\Config;

class ModulesServices
{
    /**
     * @var Module
     */
    private Module $module;

    /**
     * ModulesServices constructor.
     * @param Module $module
     */
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * @return mixed
     */
    public function list() 
    {
        return $this->module->where('status', 1)
            ->select('id', 'module_name', 'status')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * @param $request
     * @return mixed
     */
    public function show($request) 
    {
        return $this->module->findOrFail($request['id']);
    }

    /**
     * @return mixed
     */
    public function dropDown(): mixed
    {
        return $this->module->where('status', 1)
            ->select('id', 'module_name')
            ->get();
    }
}