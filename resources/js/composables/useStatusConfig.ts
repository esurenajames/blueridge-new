import { Clock, CheckCircle, XCircle, AlertCircle } from 'lucide-vue-next';

export function useStatusConfig() {
  const getStatusConfig = (status: string) => {
    switch (status) {
      case 'approved':
        return {
          icon: CheckCircle,
          variant: 'success',
          class: 'text-green-600 dark:text-green-400',
        };
      case 'pending':
        return {
          icon: Clock,
          variant: 'warning',
          class: 'text-yellow-600 dark:text-yellow-400',
        };
      case 'declined':
      case 'voided':
        return {
          icon: XCircle,
          variant: 'destructive',
          class: 'text-gray-50',
        };
      case 'returned':
        return {
          icon: AlertCircle,
          variant: 'default',
          class: 'text-blue-600 dark:text-blue-400',
        };
      default:
        return {
          icon: AlertCircle,
          variant: 'secondary',
          class: 'text-slate-600 dark:text-slate-400',
        };
    }
  };

  return {
    getStatusConfig,
  };
}