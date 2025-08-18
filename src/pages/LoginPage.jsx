import React, { useState } from "react";
import { login } from "../utils/api";  // helper login

export default function LoginPage({ onClose, onLoginSuccess }) {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  const submit = async () => {
    setLoading(true);
    setError("");

    try {
      console.log("Login submit body:", { email, password });

      const data = await login({ email, password });
      console.log("Login response:", data);

      if (data.success && data.token) {
        if (onLoginSuccess) onLoginSuccess(data.user);
        if (onClose) onClose();
      } else {
        setError(data.message || "Login gagal, cek email/password");
      }
    } catch (err) {
      console.error("Login error:", err);
      setError(err.data?.message || err.message || "Server error");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
      <div className="w-full max-w-md bg-gray-800 rounded-2xl p-6 shadow-xl text-gray-100">
        <h2 className="text-2xl font-bold text-center mb-4">Sign In</h2>
        {error && <div className="mb-3 text-red-400 text-sm text-center">{error}</div>}
        <label className="block text-sm font-semibold mb-4">
          Email
          <input
            type="email"
            required
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder="you@example.com"
            className="mt-2 w-full bg-gray-900 px-3 py-2 rounded-md outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </label>
        <label className="block text-sm font-semibold mb-4">
          Password
          <input
            type="password"
            required
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder="••••••••"
            className="mt-2 w-full bg-gray-900 px-3 py-2 rounded-md outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </label>
        <div className="flex justify-end gap-3">
          <button
            type="button"
            onClick={onClose}
            className="px-4 py-2 rounded-md bg-gray-700 hover:bg-gray-600 transition"
          >
            Cancel
          </button>
          <button
            type="button"
            onClick={submit}
            disabled={loading}
            className="px-4 py-2 rounded-md bg-indigo-600 hover:bg-indigo-500 font-semibold transition disabled:opacity-50"
          >
            {loading ? "Loading..." : "Sign In"}
          </button>
        </div>
      </div>
    </div>
  );
}