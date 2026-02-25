<!-- Payment History Modal -->
<div id="paymentHistoryModal" class="payment-history-modal">
    <div class="payment-history-overlay" id="paymentHistoryModalOverlay"></div>
    <div class="payment-history-modal-container">
        <!-- Modal Header -->
        <div class="payment-history-modal-header">
            <div class="payment-history-header-content">
                <h2 class="payment-history-modal-title">Payment history</h2>
            </div>
            <button class="payment-history-close-btn" id="closePaymentHistoryModalBtn" aria-label="Close modal">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="payment-history-modal-body">
            <!-- Payment Entries List -->
            <div class="payment-entries-list" id="paymentEntriesList">
                @if (isset($paymentHistoryData['payments']) && count($paymentHistoryData['payments']) > 0)
                    @foreach ($paymentHistoryData['payments'] as $payment)
                        <div class="payment-entry {{ $payment['unread'] ? 'unread' : '' }}"
                            data-payment-id="{{ $payment['id'] }}">
                            <div
                                class="payment-status-icon {{ in_array($payment['status'], ['approved', 'completed']) ? 'completed' : 'pending' }}">
                                <i
                                    class="fi {{ in_array($payment['status'], ['approved', 'completed']) ? 'fi-rr-check' : 'fi-rr-minus' }}"></i>
                            </div>
                            <div class="payment-entry-content">
                                <div class="payment-entry-header">
                                    <div class="payment-entry-description">
                                        <p class="payment-entry-type">
                                            {{ $payment['type'] }}: <span class="payment-entry-milestone">Milestone
                                                {{ $payment['milestoneNumber'] }}</span>
                                        </p>
                                        <p class="payment-entry-amount">{{ $payment['amount'] }}</p>
                                    </div>
                                    <div class="payment-entry-meta">
                                        <p class="payment-entry-date">{{ $payment['date'] }}</p>
                                        <p class="payment-entry-time">{{ $payment['time'] }}</p>
                                        <a href="#" class="payment-details-link"
                                            data-payment-id="{{ $payment['id'] }}">Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-gray-500 text-center py-8">No payment history available</p>
                @endif
            </div>
        </div>

        <!-- Modal Footer - Summary -->
        <div class="payment-history-modal-footer">
            <div class="payment-summary-section">
                <div class="payment-summary-item">
                    <span class="payment-summary-label">Total Estimated Project Amount:</span>
                    <span class="payment-summary-value estimated"
                        id="totalEstimatedAmount">{{ $paymentHistoryData['summary']['totalEstimated'] ?? '₱0' }}</span>
                </div>
                <div class="payment-summary-item">
                    <span class="payment-summary-label">Total Amount Paid:</span>
                    <span class="payment-summary-value paid" id="totalAmountPaid">
                        @php
                            $paidAmount = $paymentHistoryData['summary']['totalPaid'] ?? 0;
                            echo $paidAmount > 0 ? '-₱' . number_format($paidAmount, 0) : '₱0';
                        @endphp
                    </span>
                </div>
                <div class="payment-summary-item">
                    <span class="payment-summary-label">Total Remaining Amount:</span>
                    <span class="payment-summary-value remaining"
                        id="totalRemainingAmount">{{ $paymentHistoryData['summary']['totalRemaining'] ?? '₱0' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>