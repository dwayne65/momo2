import { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { toast } from 'sonner';
import { verifyUser } from '@/lib/api';
import { saveUser, getUserByPhone } from '@/lib/storage';
import { UserCheck, Phone, Calendar, User, CheckCircle, X } from 'lucide-react';

const VerifyUser = () => {
  const [phone, setPhone] = useState('');
  const [loading, setLoading] = useState(false);
  const [userData, setUserData] = useState<any>(null);
  const [dialogOpen, setDialogOpen] = useState(false);

  const handleVerify = async () => {
    if (!phone) {
      toast.error('Please enter a phone number');
      return;
    }

    setLoading(true);
    try {
      // Check if user already exists
      const existingUser = getUserByPhone(phone);
      if (existingUser) {
        setUserData(existingUser);
        toast.info('User already verified in system');
        setLoading(false);
        return;
      }

      // Call API
      const response = await verifyUser(phone);

      // Save to localStorage
      const savedUser = saveUser({
        firstName: response.firstName,
        lastName: response.lastName,
        birthDate: response.birthDate,
        gender: response.gender,
        isActive: response.isActive,
        phone: phone,
      });

      setUserData(savedUser);
      setDialogOpen(true);
      toast.success('User verified and saved successfully!');
    } catch (error) {
      toast.error('Failed to verify user. Please try again.');
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold mb-2">Verify User</h1>
        <p className="text-muted-foreground">Verify and register mobile money users</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card className="dashboard-card">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <UserCheck className="w-5 h-5" />
              User Verification
            </CardTitle>
            <CardDescription>Enter phone number to verify user identity</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="phone">Phone Number</Label>
              <Input
                id="phone"
                type="tel"
                placeholder="e.g., +250788123456"
                value={phone}
                onChange={(e) => setPhone(e.target.value)}
              />
            </div>
            <Button onClick={handleVerify} disabled={loading} className="w-full">
              {loading ? 'Verifying...' : 'Verify User'}
            </Button>
          </CardContent>
        </Card>

        <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
          <DialogContent className="sm:max-w-md">
            <DialogHeader>
              <DialogTitle className="flex items-center gap-2 text-success">
                <CheckCircle className="w-5 h-5" />
                User Verified
              </DialogTitle>
              <DialogDescription>User details retrieved successfully</DialogDescription>
            </DialogHeader>
            <div className="space-y-4">
              <div className="space-y-3">
                <div className="flex items-start gap-3 p-3 bg-muted/50 rounded-lg">
                  <User className="w-5 h-5 text-primary mt-0.5" />
                  <div>
                    <p className="text-sm text-muted-foreground">First Name</p>
                    <p className="font-medium">{userData?.firstName}</p>
                  </div>
                </div>
                <div className="flex items-start gap-3 p-3 bg-muted/50 rounded-lg">
                  <User className="w-5 h-5 text-primary mt-0.5" />
                  <div>
                    <p className="text-sm text-muted-foreground">Last Name</p>
                    <p className="font-medium">{userData?.lastName}</p>
                  </div>
                </div>
                <div className="flex items-start gap-3 p-3 bg-muted/50 rounded-lg">
                  <Calendar className="w-5 h-5 text-primary mt-0.5" />
                  <div>
                    <p className="text-sm text-muted-foreground">Birth Date</p>
                    <p className="font-medium">{userData?.birthDate}</p>
                  </div>
                </div>
                <div className="flex items-start gap-3 p-3 bg-muted/50 rounded-lg">
                  <User className="w-5 h-5 text-primary mt-0.5" />
                  <div>
                    <p className="text-sm text-muted-foreground">Gender</p>
                    <p className="font-medium">{userData?.gender}</p>
                  </div>
                </div>
                <div className="flex items-start gap-3 p-3 bg-muted/50 rounded-lg">
                  <CheckCircle className="w-5 h-5 text-success mt-0.5" />
                  <div>
                    <p className="text-sm text-muted-foreground">Active</p>
                    <p className="font-medium">{userData?.isActive ? 'Yes' : 'No'}</p>
                  </div>
                </div>
                <div className="flex items-start gap-3 p-3 bg-muted/50 rounded-lg">
                  <Phone className="w-5 h-5 text-primary mt-0.5" />
                  <div>
                    <p className="text-sm text-muted-foreground">Phone Number</p>
                    <p className="font-medium">{userData?.phone}</p>
                  </div>
                </div>
              </div>
            </div>
          </DialogContent>
        </Dialog>
      </div>
    </div>
  );
};

export default VerifyUser;
