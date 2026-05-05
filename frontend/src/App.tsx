import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter } from "react-router-dom";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { Toaster } from "@/components/ui/toaster";
import { TooltipProvider } from "@/components/ui/tooltip";
import { BootstrapProvider } from "@/app/bootstrap/BootstrapProvider";
import { DevStatePanel } from "@/app/bootstrap/DevStatePanel";
import { AppRouter } from "@/app/router";

const queryClient = new QueryClient();

const App = () => (
  <QueryClientProvider client={queryClient}>
    <TooltipProvider>
      <BrowserRouter>
        <BootstrapProvider>
          <AppRouter />
          <DevStatePanel />
        </BootstrapProvider>
      </BrowserRouter>
      <Toaster />
      <Sonner />
    </TooltipProvider>
  </QueryClientProvider>
);

export default App;
