import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { toast } from 'sonner';
import { getUsers, getPayments, getTransfers, getGroupMembers, getGroups } from '@/lib/storage';
import { FileSpreadsheet, Download } from 'lucide-react';
import * as XLSX from 'xlsx';

const Export = () => {
  const exportToExcel = (data: any[], filename: string, sheetName: string) => {
    if (data.length === 0) {
      toast.error('No data to export');
      return;
    }

    const worksheet = XLSX.utils.json_to_sheet(data);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, sheetName);
    XLSX.writeFile(workbook, `${filename}.xlsx`);
    toast.success(`${filename} exported successfully!`);
  };

  const handleExportUsers = () => {
    const users = getUsers();
    exportToExcel(users, 'users', 'Users');
  };

  const handleExportPayments = () => {
    const payments = getPayments();
    exportToExcel(payments, 'payments', 'Payments');
  };

  const handleExportTransfers = () => {
    const transfers = getTransfers();
    exportToExcel(transfers, 'transfers', 'Transfers');
  };

  const handleExportGroupMembers = () => {
    const members = getGroupMembers();
    const groups = getGroups();
    const users = getUsers();

    const enrichedData = members.map(member => {
      const group = groups.find(g => g.id === member.group_id);
      const user = users.find(u => u.id === member.user_id);
      return {
        group_name: group?.group_name || 'Unknown',
        user_name: user?.name || 'Unknown',
        user_phone: user?.phone || 'Unknown',
        user_id: user?.national_id || 'Unknown',
        added_at: member.created_at,
      };
    });

    exportToExcel(enrichedData, 'group-members', 'Group Members');
  };

  const exportOptions = [
    {
      title: 'Export Users',
      description: 'Download all verified users data',
      icon: FileSpreadsheet,
      color: 'text-primary',
      bgColor: 'bg-primary/10',
      action: handleExportUsers,
      count: getUsers().length,
    },
    {
      title: 'Export Payments',
      description: 'Download all payment transactions',
      icon: FileSpreadsheet,
      color: 'text-success',
      bgColor: 'bg-success/10',
      action: handleExportPayments,
      count: getPayments().length,
    },
    {
      title: 'Export Transfers',
      description: 'Download all transfer records',
      icon: FileSpreadsheet,
      color: 'text-warning',
      bgColor: 'bg-warning/10',
      action: handleExportTransfers,
      count: getTransfers().length,
    },
    {
      title: 'Export Group Members',
      description: 'Download group membership data',
      icon: FileSpreadsheet,
      color: 'text-info',
      bgColor: 'bg-info/10',
      action: handleExportGroupMembers,
      count: getGroupMembers().length,
    },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold mb-2">Export Data</h1>
        <p className="text-muted-foreground">Download data as Excel spreadsheets</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {exportOptions.map((option, index) => {
          const Icon = option.icon;
          return (
            <Card key={index} className="dashboard-card">
              <CardHeader>
                <div className="flex items-center justify-between mb-2">
                  <div className={`p-3 rounded-full ${option.bgColor}`}>
                    <Icon className={`w-6 h-6 ${option.color}`} />
                  </div>
                  <span className="text-2xl font-bold text-muted-foreground">{option.count}</span>
                </div>
                <CardTitle>{option.title}</CardTitle>
                <CardDescription>{option.description}</CardDescription>
              </CardHeader>
              <CardContent>
                <Button 
                  onClick={option.action} 
                  className="w-full"
                  variant="outline"
                  disabled={option.count === 0}
                >
                  <Download className="w-4 h-4 mr-2" />
                  Download Excel
                </Button>
              </CardContent>
            </Card>
          );
        })}
      </div>

      <Card className="dashboard-card bg-muted/30">
        <CardHeader>
          <CardTitle>Export Information</CardTitle>
        </CardHeader>
        <CardContent>
          <ul className="space-y-2 text-sm text-muted-foreground">
            <li>• Excel files are generated using the XLSX library</li>
            <li>• All data is exported from your browser's local storage</li>
            <li>• Files are saved directly to your downloads folder</li>
            <li>• Export buttons are disabled when no data is available</li>
          </ul>
        </CardContent>
      </Card>
    </div>
  );
};

export default Export;
