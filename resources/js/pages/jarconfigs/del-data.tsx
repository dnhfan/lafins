import React, { useState } from 'react';
import { Inertia } from '@inertiajs/inertia';
import JarsController from '@/actions/App/Http/Controllers/JarsController';


export default function DeleteAllDataBox() {
  const [checked, setChecked] = useState(false);
  const [modalOpen, setModalOpen] = useState(false);
  const [confirmText, setConfirmText] = useState('');

  function openModal() {
    setConfirmText('');
    setModalOpen(true);
  }

  function closeModal() {
    setModalOpen(false);
  }

  async function performDelete() {
  // Call the controller route to delete all financial data and reset jars
  Inertia.post(JarsController.deleteAll.url(), {}, { onSuccess: () => closeModal() });
  }

  return (
    <div>
      {/* flash messages are handled at the page level to avoid duplicate/leftover alerts */}
      <div className="p-4 rounded-md bg-red-50 border border-red-200">
        <div className="flex items-start gap-3">
          <div className="text-red-700 text-2xl">️<i className="fa-solid fa-circle-exclamation"></i></div>
          <div className="flex-1">
            <div className="flex items-center gap-3">
              <h3 className="text-lg font-semibold text-red-700">Permanently delete data</h3>
              <span className="text-sm text-red-600">(Irreversible)</span>
            </div>
            <p className="mt-2 text-sm text-red-600">This will permanently delete all jar configuration data and cannot be recovered. Please confirm if you really want to proceed.</p>

            <div className="mt-3 flex items-center gap-3">
              <label className="inline-flex items-center gap-2">
                <input type="checkbox" checked={checked} onChange={(e) => setChecked(e.target.checked)} className="w-4 h-4" />
                <span className="text-sm text-red-700">I understand the consequences and want to delete all data</span>
              </label>

              <button
                type="button"
                disabled={!checked}
                onClick={openModal}
                className={`ml-auto px-3 py-1 rounded text-white ${checked ? 'bg-red-600 hover:bg-red-700' : 'bg-red-200 text-red-400 cursor-not-allowed'}`}
              >
                Delete all data
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Modal */}
      {modalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
          <div className="w-full max-w-lg p-6 bg-white rounded shadow-lg">
            <h2 className="text-xl font-bold text-red-700">Confirm data deletion</h2>
            <p className="mt-2 text-sm text-gray-600">You are about to delete all data. This action is irreversible. Please type <strong>"DELETE"</strong> in the box below to confirm.</p>

            <div className="mt-4">
              <input
                type="text"
                value={confirmText}
                onChange={(e) => setConfirmText(e.target.value)}
                placeholder='Type "DELETE" to confirm'
                className="w-full p-2 border rounded"
              />
            </div>

            <div className="mt-4 flex justify-end gap-3">
              <button type="button" onClick={closeModal} className="px-3 py-1 rounded bg-gray-100">Cancel</button>
              <button
                type="button"
                disabled={confirmText.trim().toUpperCase() !== 'DELETE'}
                onClick={performDelete}
                className={`px-3 py-1 rounded text-white ${confirmText.trim().toUpperCase() === 'DELETE' ? 'bg-red-600 hover:bg-red-700' : 'bg-red-200 text-red-400 cursor-not-allowed'}`}
              >
                Delete all
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
