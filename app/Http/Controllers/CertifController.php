<?php

namespace App\Http\Controllers;

use Generator;
use Illuminate\Http\Request;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use App\Models\Certif;
use Spatie\PdfToImage\Pdf;

class CertifController extends Controller
{
    public function uploadFiles(Request $request)
    {
        $request->validate([
            'files.*' => 'file|mimes:pdf|max:2048',
        ]);

        $uploadFiles = $request->file('files');
        $processedFiles = [];


        foreach ($uploadFiles as $file) {
            try {
                $originalName = $file->getClientOriginalName();
                $referenceNumber = 'REF-' . uniqid();

                // Generate QR Code
                $qrCodeUrl = 'http://localhost:5174/qrcode/'.$referenceNumber;
                $qrCode = new Builder(
                    writer:new PngWriter(),
                    data: $qrCodeUrl,
                    encoding:new Encoding('UTF-8'),
                    errorCorrectionLevel:ErrorCorrectionLevel::High,
                    size:200,
                );
                $result = $qrCode->build();
                // Save QR code temporarily
                $tempQrCodePath = storage_path('app/temp/qrcode_'.time().'.png');
                $result->saveToFile($tempQrCodePath);

                // Process PDF with FPDI
                $pdf = new Fpdi();

                // Get the original PDF content
                $originalPdfContent = file_get_contents($file->getRealPath());
                file_put_contents(storage_path('app/temp/original.pdf'), $originalPdfContent);

                $pageCount = $pdf->setSourceFile(storage_path('app/temp/original.pdf'));

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($templateId);

                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);

                    // Add QR code only to first page
                    if ($pageNo === 1) {
                        $qrCodeX = $size['width'] - 30;
                        $qrCodeY = $size['height'] - 30;
                        $pdf->Image($tempQrCodePath, $qrCodeX, $qrCodeY, 25, 25);
                    }
                }

                // Save modified PDF
                $newFileName = 'processed_'. $originalName;
                $pdfPath = 'processed_pdfs/'.$newFileName;

                // Ensure directory exists
                Storage::disk('public')->makeDirectory('processed_pdfs');

                $pdf->Output(storage_path('app/public/'.$pdfPath), 'F');

                // Generate an image from the first page of the PDF
                $imageFileName = 'image_'.pathinfo($originalName, PATHINFO_FILENAME).'.png';
                $imagePath = 'processed_images/'.$imageFileName;

                // Ensure directory exists
                Storage::disk('public')->makeDirectory('processed_images');

                    // // Generate an image from the first page of the PDF using Spatie PDF To Image
                    // $imageFileName = 'image_'.pathinfo($originalName, PATHINFO_FILENAME).'.png';
                    // $imagePath = 'processed_images/'.$imageFileName;

                    // $pdfToImage = new Pdf(storage_path('app/public/'.$pdfPath));
                    // $pdfToImage->setPage(1) // Set first page
                    //             ->setOutputFormat('png')
                    //             ->saveImage(storage_path('app/public/'.$imagePath));

                $processedFiles[] = [
                    'original_name' => $originalName,
                    'reference_number' => $referenceNumber,
                    'processed_path' => $pdfPath,
                    'image_path' => $imagePath,
                    'public_url' => Storage::disk('public')->url($pdfPath),
                    'image_url' => Storage::disk('public')->url($imagePath),
                ];

                // Clean up temp files
                @unlink($tempQrCodePath);
                @unlink(storage_path('app/temp/original.pdf'));

            } catch (\Exception $e) {
                // Log error and continue with next file
                \Log::error("Error processing file {$originalName}: ".$e->getMessage());
                continue;
            }
        }

        // Save to database
        if (!empty($processedFiles)) {
            Certif::insert($processedFiles);
        }

        return response()->json([
            'success' => true,
            'message' => count($processedFiles).' file(s) processed successfully',
            'files' => $processedFiles,
        ]);
    }

    public function show($reference_number)
    {
        $certificate = Certif::where('reference_number', $reference_number)->first();

         // Préparer les données du scan
        $scanData = [
            'reference_number' => $reference_number,
            'status' => $certificate ? 'valide' : 'invalide',
            'certif_name' => $certificate ? $certificate->original_name : null,
            'scanned_at' => now(),
        ];

        // Stocker le scan en base de données
        \DB::table('scans')->insert($scanData);

        if (!$certificate){
            // Si le certificat n'est pas trouvé, retourner une réponse avec un statut invalide
            return response()->json([
                'success' => false,
                'message' => 'Certificat non trouvé',
                'status' => 'invalide',
            ]);
        }

        return response()->json([
            'success' => true,
            'certificate' => $certificate,
        ]);
    }

        public function files($reference_number)
    {
        $certificate = Certif::where('reference_number', $reference_number)->first();

         // Préparer les données du scan
        $scanData = [
            'reference_number' => $reference_number,
            'status' => $certificate ? 'valide' : 'invalide',
            'certif_name' => $certificate ? $certificate->original_name : null,
            'scanned_at' => now(),
        ];

        // Stocker le scan en base de données
        \DB::table('scans')->insert($scanData);

        if (!$certificate){
            // Si le certificat n'est pas trouvé, retourner une réponse avec un statut invalide
            return response()->json([
                'success' => false,
                'message' => 'Certificat non trouvé',
                'status' => 'invalide',
            ]);
        }

        return response()->json([
            'success' => true,
            'certificate' => $certificate,
        ]);
    }

    public function getCertifs()
    {
        // Récupérer tous les certificats
        $certifs = Certif::all();

        return response()->json([
            'success' => true,
            'data' => $certifs,
        ]);
    }
    public function download($reference_number)
    {
        $certificate = Certif::where('reference_number', $reference_number)->first();

        if (!$certificate) {
            return response()->json([
                'message' => 'Certificat non trouvé',
                'error' => 'Not Found'
            ], 404);
        }

        $filePath = storage_path('app/public/' . $certificate->processed_path);

        if (!file_exists($filePath)) {
            return response()->json([
                'message' => 'Fichier introuvable',
                'error' => 'Not Found'
            ], 404);
        }

        return response()->download($filePath, $certificate->original_name);
    }

    public function delete($reference_number)
    {
        $certificate = Certif::where('reference_number', $reference_number)->first();

        if (!$certificate) {
            return response()->json([
                'message' => 'Certificat non trouvé',
                'error' => 'Not Found'
            ], 404);
        }

        // Supprimer le fichier du stockage
        $filePath = storage_path('app/public/' . $certificate->processed_path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Supprimer l'image associée si elle existe
        if ($certificate->image_path) {
            $imagePath = storage_path('app/public/' . $certificate->image_path);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Supprimer l'enregistrement de la base de données
        $certificate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Certificat supprimé avec succès'
        ]);
    }
}
