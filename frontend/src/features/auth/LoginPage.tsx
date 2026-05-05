import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { Building2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { FormField } from "@/components/forms/FormFields";
import { LanguageSwitcher } from "@/components/layout/LanguageSwitcher";
import { useBootstrap } from "@/app/bootstrap/BootstrapProvider";
import { useT } from "@/app/bootstrap/useT";
import { mockApi } from "@/api/mockApi";

const schema = z.object({
  email: z.string().email(),
  password: z.string().min(1),
});
type FormValues = z.infer<typeof schema>;

export default function LoginPage() {
  const t = useT();
  const navigate = useNavigate();
  const { setUser, setAppState, reload } = useBootstrap();
  const [submitting, setSubmitting] = useState(false);

  const { register, handleSubmit, formState: { errors } } = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: { email: "", password: "" },
  });

  const onSubmit = async (values: FormValues) => {
    setSubmitting(true);
    try {
      const { user } = await mockApi.login(values.email, values.password);
      setUser(user);
      await reload();
      setAppState("clinic_required");
      navigate("/app/clinic-select");
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="relative flex min-h-screen items-center justify-center bg-gradient-to-br from-background to-accent/40 p-6">
      <div className="absolute end-4 top-4">
        <LanguageSwitcher />
      </div>
      <Card className="w-full max-w-md p-8">
        <div className="mb-6 flex items-center gap-3">
          <div className="flex h-10 w-10 items-center justify-center rounded-md bg-primary text-primary-foreground">
            <Building2 className="h-5 w-5" />
          </div>
          <div>
            <p className="text-base font-semibold tracking-tight">{t("app.name")}</p>
            <p className="text-xs text-muted-foreground">{t("auth.subtitle")}</p>
          </div>
        </div>
        <h1 className="text-xl font-semibold">{t("auth.welcome")}</h1>
        <form onSubmit={handleSubmit(onSubmit)} className="mt-5 space-y-4">
          <FormField
            id="email"
            label={t("auth.email")}
            type="email"
            autoComplete="email"
            required
            error={errors.email?.message}
            {...register("email")}
          />
          <FormField
            id="password"
            label={t("auth.password")}
            type="password"
            autoComplete="current-password"
            required
            error={errors.password?.message}
            {...register("password")}
          />
          <Button type="submit" className="w-full" disabled={submitting}>
            {submitting ? t("auth.signing_in") : t("auth.sign_in")}
          </Button>
        </form>
        <p className="mt-6 text-center text-xs text-muted-foreground">
          Mock login — backend auth not wired in this prototype.
        </p>
      </Card>
    </div>
  );
}
