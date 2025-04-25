import type { User } from '@/types';

export function getAvatarProps(user: User) {
    return {
        src: user.avatar || undefined,
        alt: user.name,
        fallback: getInitials(user.name),
        showAvatar: Boolean(user.avatar && user.avatar !== '')
    };
}

export function getInitials(name: string): string {
    if (!name) return '';
    
    return name
        .split(' ')
        .map(part => part[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
}