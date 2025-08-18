import React from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faRightFromBracket, faLock } from '@fortawesome/free-solid-svg-icons';

export default function Navbar({ user, onLoginClick, logout }) {
  const alias = user?.username?.charAt(0)?.toUpperCase() || '?';

  return (
    <nav className="fixed top-0 left-0 right-0 bg-gray-800/90 backdrop-blur-md z-50 border-b border-gray-700">
      <div className="max-w-7xl mx-auto flex items-center justify-between px-6 py-3">
        <div className="text-white text-lg font-bold select-none flex items-center gap-2">
          <span className="text-2xl">🗓️</span>
          <span>TaskFlow</span>
        </div>

        <div className="flex items-center gap-4">
          {user ? (
            <>
              <div className="hidden sm:flex flex-col text-sm leading-tight">
                <span className="text-gray-300 truncate max-w-xs">{user.email}</span>
                <span className="font-semibold text-white">{user.username}</span>
              </div>

              <button
                onClick={logout}
                title="Sign Out"
                className="flex items-center gap-2 bg-gray-700 hover:bg-gray-600 px-3 py-1.5 rounded-md transition"
              >
                <span className="text-xs">Sign Out</span>
                <FontAwesomeIcon icon={faRightFromBracket} />
              </button>
            </>
          ) : (
            <button
              onClick={onLoginClick}
              title="Sign In"
              className="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 px-3 py-1.5 rounded-md transition"
            >
              <span className="text-xs">Sign In</span>
              <FontAwesomeIcon icon={faLock} />
            </button>
          )}
        </div>
      </div>
    </nav>
  );
}
