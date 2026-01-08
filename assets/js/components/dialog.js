import { cn } from "../../../lib/utils.js";

function Dialog({ className, ...props }) {
  return {
    element: "div",
    className: cn("fixed inset-0 z-50 flex items-center justify-center", className),
    ...props
  };
}

function DialogContent({ className, children, ...props }) {
  return {
    element: "div",
    className: cn(
      "fixed left-[50%] top-[50%] z-50 grid w-full max-w-lg translate-x-[-50%] translate-y-[-50%] gap-4 border bg-background p-6 shadow-lg duration-200 sm:rounded-lg",
      className
    ),
    ...props
  };
}

function DialogHeader({ className, ...props }) {
  return {
    element: "div",
    className: cn(
      "flex flex-col space-y-1.5 text-center sm:text-left",
      className
    ),
    ...props
  };
}

function DialogTitle({ className, ...props }) {
  return {
    element: "h2",
    className: cn(
      "text-lg font-semibold leading-none tracking-tight",
      className
    ),
    ...props
  };
}

function DialogDescription({ className, ...props }) {
  return {
    element: "p",
    className: cn("text-sm text-muted-foreground", className),
    ...props
  };
}

function DialogFooter({ className, ...props }) {
  return {
    element: "div",
    className: cn(
      "flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2",
      className
    ),
    ...props
  };
}

// Export for use in PHP views
window.Dialog = Dialog;
window.DialogContent = DialogContent;
window.DialogHeader = DialogHeader;
window.DialogTitle = DialogTitle;
window.DialogDescription = DialogDescription;
window.DialogFooter = DialogFooter;

