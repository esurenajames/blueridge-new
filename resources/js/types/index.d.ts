import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Company {
  companyName: string;
  contactPerson: string;
  address: string;
  contactNumber: string;
  email: string;
  items: CompanyItem[];
}

export interface CompanyItem {
  name: string;
  description: string;
  price: number;
  quantity: number;
}

export interface Quotation {
  id: number;
  request_id: number;
  created_at: string;
  updated_at: string;
  details: Array<{
    id: number;
    company: {
      id: number;
      company_name: string;
      contact_person: string;
      contact_number: string;
      email: string;
      address: string;
    };
    items: Array<{
      item_name: string;
      description: string;
      quantity: number;
      price: number;
      total: number;
    }>;
  }>;
}

interface Request {
    id: number;
    title: string;
    category: string;
    description: string;
    status: string;
    progress: number;
    stages: {
      form: boolean;
      quotation: boolean;
      purchaseRequest: boolean;
      purchaseOrder: boolean;
    };
    created_at: string;
    created_by: string;
    processed_by?: string;
    processed_at?: string;
    collaborators?: Array<{
      id: number;
      name: string;
      role: string;
      permission: string;
    }>;
    files?: Array<{
      name: string;
      size: number;
      uploaded_at: string;
    }>;
    timelines?: Array<{
      id: number;
      approver_name: string;
      approved_date: string;
      approved_progress: string;
      approved_status: string;
      remarks?: string;
    }>;
    quotation?: Quotation;
}

export type BreadcrumbItemType = BreadcrumbItem;
