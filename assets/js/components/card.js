import { cn } from "../../../lib/utils.js";

function Card({ className, ...props }) {
  return {
    element: "div",
    className: cn(
      "rounded-lg border bg-card text-card-foreground shadow-sm",
      className
    ),
    ...props
  };
}

function CardHeader({ className, ...props }) {
  return {
    element: "div",
    className: cn("flex flex-col space-y-1.5 p-6", className),
    ...props
  };
}

function CardTitle({ className, ...props }) {
  return {
    element: "h3",
    className: cn(
      "text-2xl font-semibold leading-none tracking-tight",
      className
    ),
    ...props
  };
}

function CardDescription({ className, ...props }) {
  return {
    element: "p",
    className: cn("text-sm text-muted-foreground", className),
    ...props
  };
}

function CardContent({ className, ...props }) {
  return {
    element: "div",
    className: cn("p-6 pt-0", className),
    ...props
  };
}

function CardFooter({ className, ...props }) {
  return {
    element: "div",
    className: cn("flex items-center p-6 pt-0", className),
    ...props
  };
}

// Export for use in PHP views
window.Card = Card;
window.CardHeader = CardHeader;
window.CardTitle = CardTitle;
window.CardDescription = CardDescription;
window.CardContent = CardContent;
window.CardFooter = CardFooter;

