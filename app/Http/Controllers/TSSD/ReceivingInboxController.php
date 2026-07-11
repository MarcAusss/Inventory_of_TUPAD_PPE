<?php

namespace App\Http\Controllers\TSSD;

use App\Http\Controllers\Controller;
use App\Models\WorkflowNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReceivingInboxController extends Controller
{
    public function index(): View
    {
        $notifications = WorkflowNotification::query()
            ->with([
                'province',
                'callOff',
                'deliveryReceipt.items.item',
            ])
            ->forUser(Auth::id())
            ->whereIn('type', [
                'Delivery Received',
                'Receiving Discrepancy',
            ])
            ->latest()
            ->paginate(15);

        $unreadCount = WorkflowNotification::query()
            ->forUser(Auth::id())
            ->unread()
            ->whereIn('type', [
                'Delivery Received',
                'Receiving Discrepancy',
            ])
            ->count();

        return view(
            'tssd.receiving-inbox.index',
            compact(
                'notifications',
                'unreadCount'
            )
        );
    }

    public function show(
        WorkflowNotification $workflowNotification
    ): View {
        $this->ensureRecipient(
            $workflowNotification
        );

        $workflowNotification->load([
            'province',
            'callOff.distributionBatch.purchaseOrder.supplier',
            'deliveryReceipt.provinceDistribution',
            'deliveryReceipt.receivedByUser',
            'deliveryReceipt.items.item',
        ]);

        $workflowNotification->markAsRead();

        return view(
            'tssd.receiving-inbox.show',
            compact('workflowNotification')
        );
    }

    public function markAsRead(
        WorkflowNotification $workflowNotification
    ): RedirectResponse {
        $this->ensureRecipient(
            $workflowNotification
        );

        $workflowNotification->markAsRead();

        return back()->with(
            'success',
            'Notification marked as read.'
        );
    }

    public function resolve(
        WorkflowNotification $workflowNotification
    ): RedirectResponse {
        $this->ensureRecipient(
            $workflowNotification
        );

        $workflowNotification->markAsResolved();

        return redirect()
            ->route('tssd.receiving-inbox.index')
            ->with(
                'success',
                'Receiving notification marked as resolved.'
            );
    }

    private function ensureRecipient(
        WorkflowNotification $workflowNotification
    ): void {
        abort_unless(
            (int) $workflowNotification->recipient_user_id
                === (int) Auth::id(),
            403,
            'You cannot access another user’s notification.'
        );
    }
}
