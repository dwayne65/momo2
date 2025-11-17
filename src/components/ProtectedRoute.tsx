import { Navigate } from 'react-router-dom';
import { isAuthenticated } from '@/lib/auth';
import DashboardLayout from './DashboardLayout';

interface ProtectedRouteProps {
  children: React.ReactNode;
}

const ProtectedRoute = ({ children }: ProtectedRouteProps) => {
  if (!isAuthenticated()) {
    return <Navigate to="/" replace />;
  }

  return <DashboardLayout>{children}</DashboardLayout>;
};

export default ProtectedRoute;
