<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;

#[Signature('generate:uat-pdf')]
#[Description('Generate a PDF report for UAT and Blueprint')]
class GenerateUatReport extends Command
{
    public function handle()
    {
        $this->info("Generating UAT Report & Blueprint PDF...");
        
        try {
            // Render the blade template to HTML
            $html = view('reports.uat_blueprint')->render();
            
            // Load HTML and convert to PDF using DomPDF
            $pdf = Pdf::loadHTML($html);
            
            // Set paper size to A4
            $pdf->setPaper('a4', 'portrait');
            
            // Save the output PDF file to project root
            $outputFile = base_path('UAT_Report_and_Blueprint.pdf');
            $pdf->save($outputFile);
            
            $this->info("SUCCESS: PDF report saved at: {$outputFile}");
        } catch (\Exception $e) {
            $this->error("Failed to generate PDF: " . $e->getMessage());
        }
    }
}
