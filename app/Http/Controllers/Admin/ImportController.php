<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessProductImportChunk;
use App\Models\ProductImport;
use App\Services\ProductImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function __construct(protected ProductImportService $importService)
    {
    }

    public function index(): View
    {
        $imports = ProductImport::latest()->paginate(15);

        return view('admin.imports.index', compact('imports'));
    }

    public function create(): View
    {
        return view('admin.imports.create');
    }

    /**
     * Step 1: upload the CSV, read its header row, show the column
     * mapping screen with a few sample rows so the admin can confirm
     * before anything touches the database (section 26).
     */
    public function preview(Request $request): View
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:20480'],
        ]);

        $upload = $this->importService->handleUpload($request->file('csv_file'));

        return view('admin.imports.map', [
            'path' => $upload['path'],
            'headers' => $upload['headers'],
            'sampleRows' => $upload['sampleRows'],
            'systemFields' => $this->importService->systemFields,
            'originalFilename' => $request->file('csv_file')->getClientOriginalName(),
        ]);
    }

    /**
     * Step 2: admin has confirmed the column mapping. Create the
     * ProductImport tracking row and dispatch the first chunk — the job
     * chains itself until the whole file is processed (section 29).
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'path' => ['required', 'string'],
            'original_filename' => ['required', 'string'],
            'mapping' => ['required', 'array'],
        ]);

        if (! Storage::disk('local')->exists($data['path'])) {
            return back()->withErrors('The uploaded file could not be found — please upload it again.');
        }

        $totalRows = $this->importService->countRows($data['path']);

        $import = ProductImport::create([
            'user_id' => Auth::id(),
            'original_filename' => $data['original_filename'],
            'file_path' => $data['path'],
            'column_mapping' => array_filter($data['mapping']),
            'total_rows' => $totalRows,
            'status' => 'pending',
        ]);

        ProcessProductImportChunk::dispatch($import->id, 0);

        return redirect()->route('admin.imports.show', $import)
            ->with('status', 'Import started. This page will show progress as it processes.');
    }

    public function show(ProductImport $import): View
    {
        return view('admin.imports.show', compact('import'));
    }

    public function downloadErrors(ProductImport $import)
    {
        $path = "imports/errors/{$import->id}.csv";

        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, "import-{$import->id}-errors.csv");
    }
}
