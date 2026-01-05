<?php

namespace App\Http\Controllers;

use App\Data\ServiceRequest\AssignServiceRequestData;
use App\Data\ServiceRequest\CreateServiceRequestData;
use App\Data\ServiceRequest\RejectServiceRequestData;
use App\Data\ServiceRequest\ServiceRequestData;
use App\Data\ServiceRequest\UpdateServiceRequestData;
use App\Data\ServiceRequest\UpdateStatusData;
use App\Events\ServiceRequestStatusChanged;
use App\Models\ServiceRequest;
use App\Repositories\ServiceRequestRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceRequestController extends Controller
{
    public function __construct(protected ServiceRequestRepository $repository)
    {
        // Middleware can be applied here or in routes
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', ServiceRequest::class);

        $filters = $request->only([
            'status',
            'priority',
            'customer_id',
            'technician_id',
            'date_from',
            'date_to',
            'sort_by',
            'sort_direction'
        ]);

        $serviceRequests = $this->repository->getAll($filters);

        if ($request->wantsJson()) {
            return ServiceRequestData::collection($serviceRequests);
        }

        return view('service_requests.index', compact('serviceRequests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', ServiceRequest::class);

        return view('service_requests.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateServiceRequestData $data)
    {
        $this->authorize('create', ServiceRequest::class);

        try {
            $attributes = $data->toArray();
            $attributes['status'] = ServiceRequest::STATUS_PENDING;
            $attributes['approval_status'] = ServiceRequest::APPROVAL_PENDING;

            $serviceRequest = $this->repository->create($attributes);

            Log::info("Service Request created: {$serviceRequest->id}");

            if (request()->wantsJson()) {
                return ServiceRequestData::from($serviceRequest)->additional(['message' => 'Service request created successfully.']);
            }

            return redirect()->route('service-requests.show', $serviceRequest)
                ->with('success', 'Service request created successfully.');
        } catch (\Exception $e) {
            Log::error("Error creating service request: " . $e->getMessage());

            if (request()->wantsJson()) {
                return response()->json(['error' => 'Failed to create service request.'], 500);
            }

            return back()->withInput()->with('error', 'Failed to create service request.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $serviceRequest = $this->repository->findOrFail($id);
        $this->authorize('view', $serviceRequest);

        if (request()->wantsJson()) {
            return ServiceRequestData::from($serviceRequest);
        }

        return view('service_requests.show', compact('serviceRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $serviceRequest = $this->repository->findOrFail($id);
        $this->authorize('update', $serviceRequest);

        return view('service_requests.edit', compact('serviceRequest'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceRequestData $data, int $id)
    {
        $serviceRequest = $this->repository->findOrFail($id);
        $this->authorize('update', $serviceRequest);

        try {
            $this->repository->update($serviceRequest, $data->toArray());

            Log::info("Service Request updated: {$serviceRequest->id}");

            if (request()->wantsJson()) {
                return ServiceRequestData::from($serviceRequest);
            }

            return redirect()->route('service-requests.show', $serviceRequest)
                ->with('success', 'Service request updated successfully.');
        } catch (\Exception $e) {
            Log::error("Error updating service request {$id}: " . $e->getMessage());

            if (request()->wantsJson()) {
                return response()->json(['error' => 'Failed to update service request.'], 500);
            }

            return back()->withInput()->with('error', 'Failed to update service request.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $serviceRequest = $this->repository->findOrFail($id);
        $this->authorize('delete', $serviceRequest);

        try {
            $this->repository->delete($serviceRequest);

            Log::info("Service Request deleted: {$id}");

            if (request()->wantsJson()) {
                return response()->json(null, 204);
            }

            return redirect()->route('service-requests.index')
                ->with('success', 'Service request deleted successfully.');
        } catch (\Exception $e) {
            Log::error("Error deleting service request {$id}: " . $e->getMessage());

            if (request()->wantsJson()) {
                return response()->json(['error' => 'Failed to delete service request.'], 500);
            }

            return back()->with('error', 'Failed to delete service request.');
        }
    }

    /**
     * Assign a technician to the service request.
     */
    public function assign(AssignServiceRequestData $data, int $id)
    {
        $serviceRequest = $this->repository->findOrFail($id);
        $this->authorize('assign', $serviceRequest);

        try {
            $oldStatus = $serviceRequest->status;

            $updateData = $data->toArray();
            $updateData['status'] = ServiceRequest::STATUS_ASSIGNED;

            $this->repository->update($serviceRequest, $updateData);

            event(new ServiceRequestStatusChanged($serviceRequest, $oldStatus, ServiceRequest::STATUS_ASSIGNED));

            Log::info("Service Request {$id} assigned to technician {$data->technician_id}");

            if (request()->wantsJson()) {
                return ServiceRequestData::from($serviceRequest);
            }

            return back()->with('success', 'Technician assigned successfully.');
        } catch (\Exception $e) {
            Log::error("Error assigning technician to request {$id}: " . $e->getMessage());

            if (request()->wantsJson()) {
                return response()->json(['error' => 'Failed to assign technician.'], 500);
            }

            return back()->with('error', 'Failed to assign technician.');
        }
    }

    /**
     * Update the status of the service request.
     */
    public function updateStatus(UpdateStatusData $data, int $id)
    {
        $serviceRequest = $this->repository->findOrFail($id);
        $this->authorize('update', $serviceRequest);

        try {
            $oldStatus = $serviceRequest->status;
            $newStatus = $data->status;

            $updateData = ['status' => $newStatus];

            if ($newStatus === ServiceRequest::STATUS_IN_PROGRESS && !$serviceRequest->started_at) {
                $updateData['started_at'] = now();
            }

            if ($newStatus === ServiceRequest::STATUS_COMPLETED) {
                $updateData['completed_at'] = now();
            }

            $this->repository->update($serviceRequest, $updateData);

            if ($oldStatus !== $newStatus) {
                event(new ServiceRequestStatusChanged($serviceRequest, $oldStatus, $newStatus));
            }

            Log::info("Service Request {$id} status updated to {$newStatus}");

            if (request()->wantsJson()) {
                return ServiceRequestData::from($serviceRequest);
            }

            return back()->with('success', 'Status updated successfully.');
        } catch (\Exception $e) {
            Log::error("Error updating status for request {$id}: " . $e->getMessage());

            if (request()->wantsJson()) {
                return response()->json(['error' => 'Failed to update status.'], 500);
            }

            return back()->with('error', 'Failed to update status.');
        }
    }

    /**
     * Approve the service request.
     */
    public function approve(int $id)
    {
        $serviceRequest = $this->repository->findOrFail($id);
        $this->authorize('approve', $serviceRequest);

        try {
            $this->repository->update($serviceRequest, [
                'approval_status' => ServiceRequest::APPROVAL_APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            Log::info("Service Request {$id} approved by user " . auth()->id());

            if (request()->wantsJson()) {
                return ServiceRequestData::from($serviceRequest);
            }

            return back()->with('success', 'Service request approved.');
        } catch (\Exception $e) {
            Log::error("Error approving request {$id}: " . $e->getMessage());

            if (request()->wantsJson()) {
                return response()->json(['error' => 'Failed to approve request.'], 500);
            }

            return back()->with('error', 'Failed to approve request.');
        }
    }

    /**
     * Reject the service request.
     */
    public function reject(RejectServiceRequestData $data, int $id)
    {
        $serviceRequest = $this->repository->findOrFail($id);
        $this->authorize('reject', $serviceRequest);

        try {
            $this->repository->update($serviceRequest, [
                'approval_status' => ServiceRequest::APPROVAL_REJECTED,
                'rejection_reason' => $data->rejection_reason,
                'status' => ServiceRequest::STATUS_CANCELLED
            ]);

            Log::info("Service Request {$id} rejected by user " . auth()->id());

            if (request()->wantsJson()) {
                return ServiceRequestData::from($serviceRequest);
            }

            return back()->with('success', 'Service request rejected.');
        } catch (\Exception $e) {
            Log::error("Error rejecting request {$id}: " . $e->getMessage());

            if (request()->wantsJson()) {
                return response()->json(['error' => 'Failed to reject request.'], 500);
            }

            return back()->with('error', 'Failed to reject request.');
        }
    }
}
