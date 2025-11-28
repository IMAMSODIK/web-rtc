<?php

namespace App\Http\Controllers;

use App\Models\MockTestSession;
use App\Http\Requests\StoreMockTestSessionRequest;
use App\Http\Requests\UpdateMockTestSessionRequest;

class MockTestSessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMockTestSessionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(MockTestSession $mockTestSession)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MockTestSession $mockTestSession)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMockTestSessionRequest $request, MockTestSession $mockTestSession)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MockTestSession $mockTestSession)
    {
        //
    }
}
