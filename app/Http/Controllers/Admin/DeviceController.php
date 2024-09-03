<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeviceRequest; // Create this request class
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin-api'); // Ensure admin is authenticated
    }

    /**
     * Display a listing of the devices.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $devices = Device::all(); // You might want to use pagination
            return response()->json([
                'success' => true,
                'data' => $devices
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve devices: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created device in storage.
     */
    public function store(DeviceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $device = Device::create($validated);
            return response()->json([
                'success' => true,
                'data' => $device,
                'message' => 'Device created successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create device: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified device.
     */
    public function show($id): JsonResponse
    {
        try {
            $device = Device::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $device
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified device in storage.
     */
    public function update(DeviceRequest $request, $id): JsonResponse
    {
        $validated = $request->validated();

        try {
            $device = Device::findOrFail($id);
            $device->update($validated);
            return response()->json([
                'success' => true,
                'data' => $device,
                'message' => 'Device updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update device: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified device from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $device = Device::findOrFail($id);
            $device->delete();
            return response()->json([
                'success' => true,
                'message' => 'Device deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete device: ' . $e->getMessage()
            ], 500);
        }
    }
}
