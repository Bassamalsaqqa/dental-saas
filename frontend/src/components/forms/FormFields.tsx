import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { cn } from "@/lib/utils";
import { forwardRef, type InputHTMLAttributes, type ReactNode } from "react";

interface FieldShellProps {
  label?: string;
  hint?: string;
  error?: string;
  required?: boolean;
  className?: string;
  children: ReactNode;
  htmlFor?: string;
}

export function FieldShell({ label, hint, error, required, className, children, htmlFor }: FieldShellProps) {
  return (
    <div className={cn("space-y-1.5", className)}>
      {label && (
        <Label htmlFor={htmlFor} className="text-xs font-medium text-foreground">
          {label}
          {required && <span className="ms-0.5 text-destructive">*</span>}
        </Label>
      )}
      {children}
      {error ? (
        <p className="text-xs text-destructive">{error}</p>
      ) : hint ? (
        <p className="text-xs text-muted-foreground">{hint}</p>
      ) : null}
    </div>
  );
}

interface FormFieldProps extends InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  hint?: string;
  error?: string;
}

export const FormField = forwardRef<HTMLInputElement, FormFieldProps>(
  ({ label, hint, error, className, id, required, ...rest }, ref) => (
    <FieldShell label={label} hint={hint} error={error} required={required} htmlFor={id}>
      <Input id={id} ref={ref} required={required} className={className} {...rest} />
    </FieldShell>
  )
);
FormField.displayName = "FormField";

interface SelectFieldProps {
  label?: string;
  hint?: string;
  error?: string;
  required?: boolean;
  value?: string;
  onValueChange?: (v: string) => void;
  placeholder?: string;
  options: { value: string; label: string }[];
  id?: string;
}

export function SelectField({
  label,
  hint,
  error,
  required,
  value,
  onValueChange,
  placeholder,
  options,
  id,
}: SelectFieldProps) {
  return (
    <FieldShell label={label} hint={hint} error={error} required={required} htmlFor={id}>
      <Select value={value} onValueChange={onValueChange}>
        <SelectTrigger id={id}>
          <SelectValue placeholder={placeholder} />
        </SelectTrigger>
        <SelectContent>
          {options.map((o) => (
            <SelectItem key={o.value} value={o.value}>
              {o.label}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
    </FieldShell>
  );
}

interface DatePickerFieldProps {
  label?: string;
  hint?: string;
  error?: string;
  required?: boolean;
  value?: string;
  onChange?: (v: string) => void;
  id?: string;
}

export function DatePickerField({
  label,
  hint,
  error,
  required,
  value,
  onChange,
  id,
}: DatePickerFieldProps) {
  return (
    <FieldShell label={label} hint={hint} error={error} required={required} htmlFor={id}>
      <Input
        id={id}
        type="date"
        value={value ?? ""}
        onChange={(e) => onChange?.(e.target.value)}
      />
    </FieldShell>
  );
}
