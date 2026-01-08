import { cn } from "../../../lib/utils.js";

function Label({ className, ...props }) {
  return {
    element: "label",
    className: cn(
      "text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70",
      className
    ),
    ...props
  };
}

// Export for use in PHP views
window.Label = Label;

