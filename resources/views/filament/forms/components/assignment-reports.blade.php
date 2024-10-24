<!-- resources/views/filament/forms/components/assignment-reports.blade.php -->
<div>
    @if($getRecord())
        @foreach($getRecord()->users as $user)
            <div class="space-y-4 mb-4 p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="font-semibold text-lg text-black dark:text-white">
                    Laporan dari: {{ $user->name }}
                </div>

                @if($user->pivot->laporan)
                    <div class="prose dark:prose-invert max-w-none">
                        <h4 class="text-sm font-semibold text-black dark:text-white">Laporan:</h4>
                        <div class="mt-1 text-black dark:text-white">
                            {{ $user->pivot->laporan }}
                        </div>
                        @if($user->pivot->submitted_at)
                            <div class="mt-2 text-sm text-gray-500">
                                Disubmit pada: {{ $user->pivot->submitted_at->format('d M Y H:i') }}
                            </div>
                        @endif
                    </div>
                @endif

                @if($user->pivot->file_laporan)
                    <div class="mt-2">
                        <h4 class="text-sm font-semibold text-black dark:text-white">File Pendukung:</h4>
                        <div class="mt-1">
                            <a href="{{ Storage::url($user->pivot->file_laporan) }}" target="_blank"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 rounded-md transition-colors">
                                <span>Download File</span>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>