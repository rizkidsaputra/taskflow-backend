import React, { useState, useEffect } from "react";
import Navbar from "./components/Navbar";
import Dashboard from "./pages/Dashboard";
import LoginPage from "./pages/LoginPage";
import { logout } from "./utils/api";

export default function App() {
  const [user, setUser] = useState(null);
  const [showLogin, setShowLogin] = useState(false);

  useEffect(() => {
    try {
      const u = localStorage.getItem("user");
      if (u) setUser(JSON.parse(u));
    } catch (err) {
      console.error("Error parsing user from localStorage:", err);
    }
  }, []);

  return (
    <div className="min-h-screen bg-gray-900 text-gray-100">
      <Navbar
        user={user}
        onLoginClick={() => setShowLogin(true)}
        logout={() => {
          logout();       // clear token + redirect
          setUser(null);  // clear state
        }}
      />

      {showLogin && !user && (
        <LoginPage
          onLoginSuccess={(u) => {
            setUser(u);
            setShowLogin(false);
          }}
          onClose={() => setShowLogin(false)}
        />
      )}

      <Dashboard user={user} />
    </div>
  );
}