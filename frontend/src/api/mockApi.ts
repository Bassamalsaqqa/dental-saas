/**
 * SESSION 1 mock API.
 * Replace these with appApiClient calls during backend integration.
 */
import type { BootstrapState, Clinic, User } from "@/types";

const delay = (ms = 350) => new Promise<void>((r) => setTimeout(r, ms));

const mockUser: User = {
  id: "u_1",
  name: "Dr. Sarah Hassan",
  email: "sarah@dentacare.example",
};

const mockClinics: Clinic[] = [
  { id: "c_1", name: "DentaCare Downtown", city: "Cairo", status: "active" },
  { id: "c_2", name: "Smile Studio Heliopolis", city: "Cairo", status: "active" },
  { id: "c_3", name: "Pearl Dental Alex", city: "Alexandria", status: "active" },
];

export const mockApi = {
  async login(_email: string, _password: string): Promise<{ user: User }> {
    await delay();
    return { user: mockUser };
  },
  async logout(): Promise<void> {
    await delay(150);
  },
  async bootstrap(): Promise<BootstrapState> {
    await delay();
    return {
      app_state: "clinic_required",
      user: mockUser,
      clinics: mockClinics,
      active_clinic: null,
      locale: "en",
      direction: "ltr",
      permissions: ["*"],
      features: ["patients", "appointments", "clinical", "finance", "inventory"],
    };
  },
  async switchClinic(clinicId: string): Promise<{ active_clinic: Clinic }> {
    await delay(200);
    const clinic = mockClinics.find((c) => c.id === clinicId);
    if (!clinic) throw new Error("clinic_not_found");
    return { active_clinic: clinic };
  },
};
