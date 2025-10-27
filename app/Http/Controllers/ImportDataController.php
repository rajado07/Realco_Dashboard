<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ImportData;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportDataController extends Controller
{

    public function index()
    {
        $data = ImportData::all();
        return response()->json($data);
    }

    public function import(Request $request)
    {
        try {
            // Validasi file
            $request->validate([
                'type' => 'required|string',
                'file' => 'required|file|max:5120', // Maksimum 5MB
            ]);

            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();

            // Parsing data berdasarkan format file
            if ($extension === 'csv') {
                $data = $this->parseCsv($file);
            } elseif (in_array($extension, ['xls', 'xlsx'])) {
                $data = $this->parseExcel($file);
            } else {
                return response()->json(['message' => 'Invalid file format.', 'type' => 'error'], 400);
            }

            // Simpan ke database
            $import = new ImportData();
            $import->type = $request->input('type');
            $import->file_name = $file->getClientOriginalName();
            $import->retrieved_at = Carbon::now();
            $import->data = json_encode($data); // Simpan JSON yang telah dibersihkan
            $import->status = 1;
            $import->message = null;
            $import->save();

            return response()->json([
                'message' => 'Data Imported Successfully',
                'type' => 'success'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed: ' . $e->getMessage(),
                'type' => 'error'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed: ' . $e->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }

    private function parseCsv($file)
    {
        $data = [];
        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            $line = fgets($handle);  // Ambil baris pertama
            $separator = strpos($line, ',') !== false ? ',' : ';'; // Tentukan separator yang digunakan

            rewind($handle); // Kembali ke awal file untuk membaca seluruhnya
            $headers = fgetcsv($handle, 0, $separator); // Ambil header

            while (($row = fgetcsv($handle, 0, $separator)) !== FALSE) {
                $rowData = array_combine($headers, $row);
                $data[] = $this->cleanRow($rowData);
            }
            fclose($handle);
        }
        return array_values(array_filter($data)); // Hapus baris kosong
    }

    private function parseExcel($file)
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getSheet(0); // Ambil sheet pertama saja
        $rows = $sheet->toArray(null, true, true, true);

        $data = [];

        if (count($rows) > 1) {
            $headers = array_shift($rows); // Ambil header
            foreach ($rows as $row) {
                $rowData = array_combine($headers, $row);
                $data[] = $this->cleanRow($rowData); // Simpan tanpa nama sheet
            }
        }

        return $data; // Kembalikan langsung array data tanpa nama sheet
    }

    private function cleanRow($row)
    {
        return array_filter(array_map(function ($value) {
            return is_string($value) ? trim(trim($value, '"')) : $value;
        }, $row), function ($value) {
            return !empty($value) || $value === "0";
        });
    }
}
