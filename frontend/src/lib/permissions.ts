// Mock permission helpers. Backend remains the authority.
export function hasPermission(perms: string[], required: string): boolean {
  return perms.includes("*") || perms.includes(required);
}
