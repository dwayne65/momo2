import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'sonner';
import { saveGroup, getGroups, addMemberToGroup, getUsers, getMembersByGroup } from '@/lib/storage';
import { Users, Plus, UserPlus } from 'lucide-react';

const Groups = () => {
  const [groupName, setGroupName] = useState('');
  const [selectedGroup, setSelectedGroup] = useState('');
  const [selectedUser, setSelectedUser] = useState('');
  const [groups, setGroups] = useState(getGroups());
  const [users, setUsers] = useState(getUsers());

  const handleCreateGroup = () => {
    if (!groupName) {
      toast.error('Please enter a group name');
      return;
    }

    saveGroup(groupName);
    setGroups(getGroups());
    setGroupName('');
    toast.success('Group created successfully!');
  };

  const handleAddMember = () => {
    if (!selectedGroup || !selectedUser) {
      toast.error('Please select both group and user');
      return;
    }

    addMemberToGroup(selectedGroup, selectedUser);
    setSelectedUser('');
    toast.success('User added to group successfully!');
  };

  const getGroupMemberCount = (groupId: string) => {
    return getMembersByGroup(groupId).length;
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold mb-2">Group Management</h1>
        <p className="text-muted-foreground">Create and manage user groups</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card className="dashboard-card">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Plus className="w-5 h-5" />
              Create New Group
            </CardTitle>
            <CardDescription>Add a new group to organize users</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="groupName">Group Name</Label>
              <Input
                id="groupName"
                placeholder="e.g., Premium Members"
                value={groupName}
                onChange={(e) => setGroupName(e.target.value)}
              />
            </div>
            <Button onClick={handleCreateGroup} className="w-full">
              Create Group
            </Button>
          </CardContent>
        </Card>

        <Card className="dashboard-card">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <UserPlus className="w-5 h-5" />
              Add User to Group
            </CardTitle>
            <CardDescription>Assign users to existing groups</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="group">Select Group</Label>
              <Select value={selectedGroup} onValueChange={setSelectedGroup}>
                <SelectTrigger id="group">
                  <SelectValue placeholder="Choose a group" />
                </SelectTrigger>
                <SelectContent>
                  {groups.map((group) => (
                    <SelectItem key={group.id} value={group.id}>
                      {group.group_name}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div className="space-y-2">
              <Label htmlFor="user">Select User</Label>
              <Select value={selectedUser} onValueChange={setSelectedUser}>
                <SelectTrigger id="user">
                  <SelectValue placeholder="Choose a user" />
                </SelectTrigger>
                <SelectContent>
                  {users.map((user) => (
                    <SelectItem key={user.id} value={user.id}>
                      {user.name} ({user.phone})
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <Button onClick={handleAddMember} className="w-full" disabled={groups.length === 0 || users.length === 0}>
              Add to Group
            </Button>
          </CardContent>
        </Card>
      </div>

      <Card className="dashboard-card">
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Users className="w-5 h-5" />
            All Groups ({groups.length})
          </CardTitle>
        </CardHeader>
        <CardContent>
          {groups.length === 0 ? (
            <p className="text-muted-foreground text-center py-8">No groups created yet</p>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {groups.map((group) => (
                <div key={group.id} className="p-4 bg-muted/50 rounded-lg border border-border">
                  <h3 className="font-semibold mb-2">{group.group_name}</h3>
                  <p className="text-sm text-muted-foreground">
                    {getGroupMemberCount(group.id)} member(s)
                  </p>
                  <p className="text-xs text-muted-foreground mt-1">
                    Created: {new Date(group.created_at).toLocaleDateString()}
                  </p>
                </div>
              ))}
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
};

export default Groups;
