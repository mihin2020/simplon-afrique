<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Note globale de l'administrateur</h3>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <form wire:submit="save">
        <div class="space-y-4">
            <div>
                <label for="adminGlobalScore" class="block text-sm font-medium text-gray-700 mb-2">
                    Note globale (0-20)
                </label>
                <input
                    type="number"
                    id="adminGlobalScore"
                    wire:model="adminGlobalScore"
                    min="0"
                    max="20"
                    step="0.1"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    placeholder="Ex: 15.5"
                >
                @error('adminGlobalScore')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-xs text-blue-800">
                    <strong>Note :</strong> Cette note sera intégrée dans le calcul final avec une pondération de 20% 
                    (80% pour les notes du jury, 20% pour la note de l'administrateur).
                </p>
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium"
                >
                    Enregistrer
                </button>
            </div>
        </div>
    </form>

    @if($candidature->admin_global_score !== null)
        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-600">
                Note actuelle : <span class="font-semibold text-gray-900">{{ number_format($candidature->admin_global_score, 2) }}/20</span>
            </p>
        </div>
    @endif
</div>
