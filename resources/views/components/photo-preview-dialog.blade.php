<dialog data-photo-preview-dialog class="m-auto max-h-[92vh] max-w-[min(94vw,64rem)] overflow-hidden rounded-2xl border-0 bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/80">
    <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-4 py-3">
        <p data-photo-preview-title class="truncate text-sm font-semibold">Pratinjau foto</p>
        <button type="button" data-photo-preview-close aria-label="Tutup pratinjau foto" class="grid size-9 shrink-0 place-items-center rounded-lg text-xl text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-600">&times;</button>
    </div>
    <div class="grid max-h-[calc(92vh-4rem)] min-h-40 place-items-center bg-slate-950 p-2 sm:p-4">
        <img data-photo-preview-image src="" alt="" class="max-h-[calc(92vh-5rem)] max-w-full object-contain">
    </div>
</dialog>
