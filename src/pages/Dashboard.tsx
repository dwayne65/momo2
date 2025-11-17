import { getUsers, getPayments, getTransfers, getGroups } from '@/lib/storage';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Users, CreditCard, ArrowRightLeft, Folder } from 'lucide-react';

const Dashboard = () => {
  const users = getUsers();
  const payments = getPayments();
  const transfers = getTransfers();
  const groups = getGroups();

  const totalPayments = payments.reduce((sum, p) => sum + p.amount, 0);
  const totalTransfers = transfers.reduce((sum, t) => sum + t.amount, 0);

  const stats = [
    {
      title: 'Total Users',
      value: users.length,
      icon: Users,
      color: 'text-primary',
      bgColor: 'bg-primary/10',
    },
    {
      title: 'Total Groups',
      value: groups.length,
      icon: Folder,
      color: 'text-info',
      bgColor: 'bg-info/10',
    },
    {
      title: 'Total Payments',
      value: `$${totalPayments.toLocaleString()}`,
      icon: CreditCard,
      color: 'text-success',
      bgColor: 'bg-success/10',
    },
    {
      title: 'Total Transfers',
      value: `$${totalTransfers.toLocaleString()}`,
      icon: ArrowRightLeft,
      color: 'text-warning',
      bgColor: 'bg-warning/10',
    },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold mb-2">Dashboard</h1>
        <p className="text-muted-foreground">Welcome to Mobile Money Management System</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {stats.map((stat, index) => {
          const Icon = stat.icon;
          return (
            <Card key={index} className="dashboard-card">
              <CardHeader className="flex flex-row items-center justify-between pb-2">
                <CardTitle className="text-sm font-medium text-muted-foreground">
                  {stat.title}
                </CardTitle>
                <div className={`p-2 rounded-full ${stat.bgColor}`}>
                  <Icon className={`w-5 h-5 ${stat.color}`} />
                </div>
              </CardHeader>
              <CardContent>
                <div className="text-3xl font-bold">{stat.value}</div>
              </CardContent>
            </Card>
          );
        })}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card className="dashboard-card">
          <CardHeader>
            <CardTitle>Recent Payments</CardTitle>
          </CardHeader>
          <CardContent>
            {payments.length === 0 ? (
              <p className="text-muted-foreground text-center py-4">No payments yet</p>
            ) : (
              <div className="space-y-3">
                {payments.slice(-5).reverse().map((payment) => (
                  <div key={payment.id} className="flex justify-between items-center p-3 bg-muted/50 rounded-lg">
                    <div>
                      <p className="font-medium">{payment.phone}</p>
                      <p className="text-sm text-muted-foreground">{payment.reference}</p>
                    </div>
                    <div className="text-right">
                      <p className="font-bold text-success">${payment.amount}</p>
                      <p className="text-xs text-muted-foreground">{payment.status}</p>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </CardContent>
        </Card>

        <Card className="dashboard-card">
          <CardHeader>
            <CardTitle>Recent Transfers</CardTitle>
          </CardHeader>
          <CardContent>
            {transfers.length === 0 ? (
              <p className="text-muted-foreground text-center py-4">No transfers yet</p>
            ) : (
              <div className="space-y-3">
                {transfers.slice(-5).reverse().map((transfer) => (
                  <div key={transfer.id} className="flex justify-between items-center p-3 bg-muted/50 rounded-lg">
                    <div>
                      <p className="font-medium">{transfer.receiver}</p>
                      <p className="text-xs text-muted-foreground">
                        {new Date(transfer.created_at).toLocaleDateString()}
                      </p>
                    </div>
                    <div className="text-right">
                      <p className="font-bold text-warning">${transfer.amount}</p>
                      <p className="text-xs text-muted-foreground">{transfer.status}</p>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default Dashboard;
