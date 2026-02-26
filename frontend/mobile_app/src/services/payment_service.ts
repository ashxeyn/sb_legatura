import { api_request } from '../config/api';

interface ApiResponse {
  success: boolean;
  message?: string;
  data?: any;
  status?: number;
}

export class payment_service {
  /**
   * Upload a payment receipt (Owner)
   */
  static async upload_payment(
    itemId: number,
    projectId: number,
    amount: number,
    paymentType: string,
    transactionNumber: string | null,
    transactionDate: string,
    receiptPhoto: { uri: string; name: string; type: string } | null
  ): Promise<ApiResponse> {
    try {
      const formData = new FormData();
      formData.append('item_id', itemId.toString());
      formData.append('project_id', projectId.toString());
      formData.append('amount', amount.toString());
      formData.append('payment_type', paymentType);
      formData.append('transaction_date', transactionDate);
      
      if (transactionNumber) {
        formData.append('transaction_number', transactionNumber);
      }

      if (receiptPhoto) {
        formData.append('receipt_photo', {
          uri: receiptPhoto.uri,
          name: receiptPhoto.name,
          type: receiptPhoto.type,
        } as any);
      }

      const response = await api_request('/api/owner/payment/upload', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
        },
        body: formData,
      });

      return response;
    } catch (error) {
      console.error('Error uploading payment:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to upload payment',
        status: 500,
      };
    }
  }

  /**
   * Get payments for a milestone item
   */
  static async get_payments_by_item(itemId: number): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/milestone-items/${itemId}/payments`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      });

      return response;
    } catch (error) {
      console.error('Error fetching payments:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to fetch payments',
        status: 500,
      };
    }
  }

  /**
   * Update a payment
   */
  static async update_payment(
    paymentId: number,
    amount?: number,
    paymentType?: string,
    transactionNumber?: string,
    transactionDate?: string,
    receiptPhoto?: { uri: string; name: string; type: string }
  ): Promise<ApiResponse> {
    try {
      const formData = new FormData();
      
      if (amount !== undefined) {
        formData.append('amount', amount.toString());
      }
      if (paymentType) {
        formData.append('payment_type', paymentType);
      }
      if (transactionNumber) {
        formData.append('transaction_number', transactionNumber);
      }
      if (transactionDate) {
        formData.append('transaction_date', transactionDate);
      }
      if (receiptPhoto) {
        formData.append('receipt_photo', {
          uri: receiptPhoto.uri,
          name: receiptPhoto.name,
          type: receiptPhoto.type,
        } as any);
      }

      const response = await api_request(`/api/owner/payment/${paymentId}`, {
        method: 'PUT',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
        },
        body: formData,
      });

      return response;
    } catch (error) {
      console.error('Error updating payment:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to update payment',
        status: 500,
      };
    }
  }

  /**
   * Delete a payment
   */
  static async delete_payment(paymentId: number, reason: string): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/owner/payment/${paymentId}`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ reason }),
      });

      return response;
    } catch (error) {
      console.error('Error deleting payment:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to delete payment',
        status: 500,
      };
    }
  }

  /**
   * Get payment history for a project
   */
  static async get_payments_by_project(projectId: number): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/projects/${projectId}/payments`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      });

      return response;
    } catch (error) {
      console.error('Error fetching payment history:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to fetch payment history',
        status: 500,
      };
    }
  }

  /**
   * Approve a payment (Contractor)
   */
  static async approve_payment(paymentId: number, userId?: number): Promise<ApiResponse> {
    try {
      const body: any = {};
      if (userId) body.user_id = userId;

      const response = await api_request(`/api/payments/${paymentId}/approve`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(body),
      });

      return response;
    } catch (error) {
      console.error('Error approving payment:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to approve payment',
        status: 500,
      };
    }
  }

  /**
   * Reject a payment (Contractor)
   */
  static async reject_payment(paymentId: number, userId: number, reason: string): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/payments/${paymentId}/reject`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ user_id: userId, reason }),
      });

      return response;
    } catch (error) {
      console.error('Error rejecting payment:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to reject payment',
        status: 500,
      };
    }
  }

  /**
   * Get downpayment receipts for a project
   */
  static async get_downpayment_receipts(projectId: number): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/projects/${projectId}/downpayment-receipts`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      });

      return response;
    } catch (error) {
      console.error('Error fetching downpayment receipts:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to fetch downpayment receipts',
        status: 500,
      };
    }
  }

  /**
   * Get backend-computed payment summary for a milestone item
   * (includes adjusted_cost, carry_forward_amount, over_amount from allocation logic)
   */
  static async get_item_payment_summary(itemId: number): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/milestone-items/${itemId}/payment-summary`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      });

      return response;
    } catch (error) {
      console.error('Error fetching item payment summary:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to fetch payment summary',
        status: 500,
      };
    }
  }
}
