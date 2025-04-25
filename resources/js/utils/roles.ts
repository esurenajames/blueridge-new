export function getDisplayRole(role: string): string {
    switch (role.toLowerCase()) {
        case 'official':
            return 'Barangay Official';
        case 'admin':
            return 'Administrator';
        case 'captain':
            return 'Barangay Captain';
        default:
            return role;
    }
}