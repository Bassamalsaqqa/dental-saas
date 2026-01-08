import { cn } from "../../../lib/utils.js";

function Table({ className, ...props }) {
  return {
    element: "div",
    className: cn("relative w-full overflow-auto", className),
    ...props
  };
}

function TableElement({ className, ...props }) {
  return {
    element: "table",
    className: cn("w-full caption-bottom text-sm", className),
    ...props
  };
}

function TableHeader({ className, ...props }) {
  return {
    element: "thead",
    className: cn("[&_tr]:border-b", className),
    ...props
  };
}

function TableBody({ className, ...props }) {
  return {
    element: "tbody",
    className: cn("[&_tr:last-child]:border-0", className),
    ...props
  };
}

function TableFooter({ className, ...props }) {
  return {
    element: "tfoot",
    className: cn(
      "border-t bg-muted/50 font-medium [&>tr]:last:border-b-0",
      className
    ),
    ...props
  };
}

function TableRow({ className, ...props }) {
  return {
    element: "tr",
    className: cn(
      "border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted",
      className
    ),
    ...props
  };
}

function TableHead({ className, ...props }) {
  return {
    element: "th",
    className: cn(
      "h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0",
      className
    ),
    ...props
  };
}

function TableCell({ className, ...props }) {
  return {
    element: "td",
    className: cn("p-4 align-middle [&:has([role=checkbox])]:pr-0", className),
    ...props
  };
}

function TableCaption({ className, ...props }) {
  return {
    element: "caption",
    className: cn("mt-4 text-sm text-muted-foreground", className),
    ...props
  };
}

// Export for use in PHP views
window.Table = Table;
window.TableElement = TableElement;
window.TableHeader = TableHeader;
window.TableBody = TableBody;
window.TableFooter = TableFooter;
window.TableRow = TableRow;
window.TableHead = TableHead;
window.TableCell = TableCell;
window.TableCaption = TableCaption;

