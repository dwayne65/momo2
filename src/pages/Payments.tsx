import { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { toast } from 'sonner';
import { processPayment } from '@/lib/api';
import { savePayment, getPayments } from '@/lib/storage';
import { CreditCard, DollarSign } from 'lucide-react';

const Payments = () => {
  const [phone, setPhone] = useState('');
  const [amount, setAmount] = useState('');
  const [loading, setLoading] = useState(false);
  const [payments, setPayments] = useState(getPayments());

  const handlePayment = async () => {
    if (!phone || !amount) {
      toast.error('Please fill all fields');
      return;
    }

    const amountNum = parseFloat(amount);
    if (isNaN(amountNum) || amountNum <= 0) {
      toast.error('Please enter a valid amount');
      return;
    }

    // Clean phone number by removing '+'
    const cleanPhone = phone.replace(/^\+/, '');

    setLoading(true);
    try {
      const response = await processPayment({
        amount: amountNum,
        currency: 'RWF',
        phone: cleanPhone,
        payment_mode: 'momo',
        message: 'Payment',
        callback_url: 'http://localhost:3000/callback',
        transfers: [{ phone: cleanPhone, amount: amountNum, message: 'Payment' }],
      });

      savePayment({
        phone,
        amount: amountNum,
        reference: response.reference || `REF-${Date.now()}`,
        status: response.status || 'completed',
      });

      setPayments(getPayments());
      setPhone('');
      setAmount('');
      toast.success('Payment processed successfully!');
    } catch (error) {
      toast.error('Payment failed. Please try again.');
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold mb-2">Payments</h1>
        <p className="text-muted-foreground">Process mobile money payments</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card className="dashboard-card">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <CreditCard className="w-5 h-5" />
              Process Payment
            </CardTitle>
            <CardDescription>Initiate a new payment transaction</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="payPhone">Phone Number</Label>
              <Input
                id="payPhone"
                type="tel"
                placeholder="e.g., +250788123456"
                value={phone}
                onChange={(e) => setPhone(e.target.value)}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="amount">Amount ($)</Label>
              <Input
                id="amount"
                type="number"
                placeholder="0.00"
                value={amount}
                onChange={(e) => setAmount(e.target.value)}
              />
            </div>
            <Button onClick={handlePayment} disabled={loading} className="w-full btn-success">
              {loading ? 'Processing...' : 'Process Payment'}
            </Button>
          </CardContent>
        </Card>

        <Card className="dashboard-card">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <DollarSign className="w-5 h-5" />
              Payment Summary
            </CardTitle>
            <CardDescription>Overview of payment statistics</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <div className="p-4 bg-success/10 border border-success/20 rounded-lg">
                <p className="text-sm text-muted-foreground mb-1">Total Payments</p>
                <p className="text-3xl font-bold text-success">
                  ${payments.reduce((sum, p) => sum + p.amount, 0).toLocaleString()}
                </p>
              </div>
              <div className="p-4 bg-muted/50 rounded-lg">
                <p className="text-sm text-muted-foreground mb-1">Total Transactions</p>
                <p className="text-2xl font-bold">{payments.length}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <Card className="dashboard-card">
        <CardHeader>
          <CardTitle>Payment History</CardTitle>
        </CardHeader>
        <CardContent>
          {payments.length === 0 ? (
            <p className="text-muted-foreground text-center py-8">No payments yet</p>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b border-border">
                    <th className="text-left p-3 text-sm font-medium text-muted-foreground">Phone</th>
                    <th className="text-left p-3 text-sm font-medium text-muted-foreground">Amount</th>
                    <th className="text-left p-3 text-sm font-medium text-muted-foreground">Reference</th>
                    <th className="text-left p-3 text-sm font-medium text-muted-foreground">Status</th>
                    <th className="text-left p-3 text-sm font-medium text-muted-foreground">Date</th>
                  </tr>
                </thead>
                <tbody>
                  {payments.slice().reverse().map((payment) => (
                    <tr key={payment.id} className="border-b border-border hover:bg-muted/30">
                      <td className="p-3 font-medium">{payment.phone}</td>
                      <td className="p-3 font-bold text-success">${payment.amount}</td>
                      <td className="p-3 text-sm text-muted-foreground">{payment.reference}</td>
                      <td className="p-3">
                        <span className="px-2 py-1 bg-success/10 text-success text-xs rounded-full">
                          {payment.status}
                        </span>
                      </td>
                      <td className="p-3 text-sm text-muted-foreground">
                        {new Date(payment.created_at).toLocaleDateString()}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
};

export default Payments;
