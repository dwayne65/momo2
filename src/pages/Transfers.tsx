import { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { toast } from 'sonner';
import { processTransfer } from '@/lib/api';
import { saveTransfer, getTransfers } from '@/lib/storage';
import { ArrowRightLeft, TrendingUp } from 'lucide-react';

const Transfers = () => {
  const [receiver, setReceiver] = useState('');
  const [amount, setAmount] = useState('');
  const [message, setMessage] = useState('');
  const [loading, setLoading] = useState(false);
  const [transfers, setTransfers] = useState(getTransfers());

  const handleTransfer = async () => {
    if (!receiver || !amount) {
      toast.error('Please fill all fields');
      return;
    }

    const amountNum = parseFloat(amount);
    if (isNaN(amountNum) || amountNum <= 0) {
      toast.error('Please enter a valid amount');
      return;
    }

    setLoading(true);
    try {
      const response = await processTransfer({ phone: receiver, amount: amountNum, message });

      saveTransfer({
        receiver,
        amount: amountNum,
        status: response.status || 'completed',
      });

      setTransfers(getTransfers());
      setReceiver('');
      setAmount('');
      setMessage('');
      toast.success('Transfer completed successfully!');
    } catch (error) {
      toast.error('Transfer failed. Please try again.');
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold mb-2">Transfers</h1>
        <p className="text-muted-foreground">Send money to other mobile money users</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card className="dashboard-card">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <ArrowRightLeft className="w-5 h-5" />
              Make Transfer
            </CardTitle>
            <CardDescription>Transfer funds to another account</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="receiver">Receiver Phone Number</Label>
              <Input
                id="receiver"
                type="tel"
                placeholder="e.g., +250788123456"
                value={receiver}
                onChange={(e) => setReceiver(e.target.value)}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="transferAmount">Amount ($)</Label>
              <Input
                id="transferAmount"
                type="number"
                placeholder="0.00"
                value={amount}
                onChange={(e) => setAmount(e.target.value)}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="message">Message (Optional)</Label>
              <Input
                id="message"
                type="text"
                placeholder="Optional message"
                value={message}
                onChange={(e) => setMessage(e.target.value)}
              />
            </div>
            <Button onClick={handleTransfer} disabled={loading} className="w-full btn-warning">
              {loading ? 'Processing...' : 'Send Transfer'}
            </Button>
          </CardContent>
        </Card>

        <Card className="dashboard-card">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <TrendingUp className="w-5 h-5" />
              Transfer Summary
            </CardTitle>
            <CardDescription>Overview of transfer statistics</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <div className="p-4 bg-warning/10 border border-warning/20 rounded-lg">
                <p className="text-sm text-muted-foreground mb-1">Total Transferred</p>
                <p className="text-3xl font-bold text-warning">
                  ${transfers.reduce((sum, t) => sum + t.amount, 0).toLocaleString()}
                </p>
              </div>
              <div className="p-4 bg-muted/50 rounded-lg">
                <p className="text-sm text-muted-foreground mb-1">Total Transfers</p>
                <p className="text-2xl font-bold">{transfers.length}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <Card className="dashboard-card">
        <CardHeader>
          <CardTitle>Transfer History</CardTitle>
        </CardHeader>
        <CardContent>
          {transfers.length === 0 ? (
            <p className="text-muted-foreground text-center py-8">No transfers yet</p>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b border-border">
                    <th className="text-left p-3 text-sm font-medium text-muted-foreground">Receiver</th>
                    <th className="text-left p-3 text-sm font-medium text-muted-foreground">Amount</th>
                    <th className="text-left p-3 text-sm font-medium text-muted-foreground">Status</th>
                    <th className="text-left p-3 text-sm font-medium text-muted-foreground">Date</th>
                  </tr>
                </thead>
                <tbody>
                  {transfers.slice().reverse().map((transfer) => (
                    <tr key={transfer.id} className="border-b border-border hover:bg-muted/30">
                      <td className="p-3 font-medium">{transfer.receiver}</td>
                      <td className="p-3 font-bold text-warning">${transfer.amount}</td>
                      <td className="p-3">
                        <span className="px-2 py-1 bg-warning/10 text-warning text-xs rounded-full">
                          {transfer.status}
                        </span>
                      </td>
                      <td className="p-3 text-sm text-muted-foreground">
                        {new Date(transfer.created_at).toLocaleDateString()}
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

export default Transfers;
