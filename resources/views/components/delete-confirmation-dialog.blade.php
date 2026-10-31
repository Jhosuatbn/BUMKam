<dialog data-delete-confirmation-dialog aria-labelledby="delete-confirmation-title" class="m-auto w-[min(92vw,28rem)] rounded-2xl border-0 bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/60 dark:bg-slate-900 dark:text-slate-100">
    <div class="p-6 sm:p-7">
        <div class="flex items-start gap-4">
            <span class="grid size-12 shrink-0 place-items-center rounded-full bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300" aria-hidden="true">
                <i class="icon-base bx bx-trash icon-lg"></i>
            </span>
            <div>
                <h2 id="delete-confirmation-title" class="text-lg font-bold">Konfirmasi Penghapusan</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">Apakah Anda yakin ingin menghapus data ini?</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
        </div>
        <div class="mt-7 flex flex-col-reverse justify-end gap-2 sm:flex-row">
            <button type="button" data-delete-cancel class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Batal</button>
            <button type="button" data-delete-confirm class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600"><i class="icon-base bx bx-trash" aria-hidden="true"></i>Ya, hapus data</button>
        </div>
    </div>
</dialog>
