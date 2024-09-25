<?php

namespace App\Http\Controllers;

use App\Models\Screen;
use App\Models\ScreenResume;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use OpenAI\Laravel\Facades\OpenAI;

class Services extends Controller
{
    const GPT_4_COST_PER_1K_TOKENS = 0.06; // USD per 1000 tokens for GPT-4
    const GPT_3_5_COST_PER_1K_TOKENS = 0.002; // USD per 1000 tokens for GPT-3.5 Turbo

    /**
     * Analyzes resumes by extracting text and making an OpenAI API call.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyze_resumes(Request $request)
    {
        $uploadedFiles = [];

        if ($request->hasFile("resumes")) {
            foreach ($request->file("resumes") as $resume) {
                $resumeName = $resume->getClientOriginalName();
                $uploadPath = './assets/resumes/';
                $resumePid = uniqid();
                $newName = $resumePid . "-" . $resumeName;
                $resume->move($uploadPath, $newName);
                $uploadedFiles[$resumePid] = $newName;
            }

            $resumeTextObj = $this->generate_resume_object($uploadedFiles);

            $analysisObj = [
                "jd" => $request->job_description,
                "resume_text" => $resumeTextObj
            ];

            // Prepare the prompt for OpenAI
            $prompt = [
                "instruction" => "You are given a job description and multiple resumes. Extract the candidate's name from each resume, match the resume with the job description, and assign a score out of 10 based on how well the candidate fits the job. Return the names of the candidates along with their scores, strong points, weak points, and a comprehensive explanation of the scoring in **valid JSON format**. Ensure that the JSON output is properly structured without any extra formatting like newlines or slashes, and is ready for parsing directly. Return candidates in descending order of scores out of 10",
                "job_description" => $analysisObj['jd'],
                "resumes" => []
            ];

            foreach ($analysisObj['resume_text'] as $resumeText) {
                $prompt["resumes"][] = ["resume_text" => $resumeText];
            }

            $promptJson = json_encode($prompt);
            $charCount = strlen($promptJson);
            $tokenCount = intval($charCount / 4); // approximate tokens
            $costEstimate = $this->calculate_openai_cost($tokenCount, 'gpt-3.5-turbo-instruct'); // estimate cost

            $costInInr = $costEstimate * 83.66;

            if ($costInInr > 50) {
                return response()->json([
                    "result" => "failure",
                    "message" => "Request too expensive, please reduce the number of resumes."
                ]);
            }

            // Call the OpenAI API for analysis
            $completion = OpenAI::completions()->create([
                'model' => 'gpt-3.5-turbo-instruct',
                'prompt' => $promptJson,
                'max_tokens' => 1500,
                'temperature' => 0.7,
            ]);

            $responseJson = $completion["choices"][0]["text"];
            return response()->json(json_decode(trim($responseJson), true)); // Return response as JSON
        } else {
            return response()->json([
                "result" => "failure",
                "message" => "Please upload PDFs"
            ]);
        }
    }

    /**
     * Extracts text from uploaded resumes.
     *
     * @param array $uploadedFiles
     * @return array
     */
    private function generate_resume_object($uploadedFiles)
    {
        $resumeTextObj = [];
        $parser = new Parser();

        foreach ($uploadedFiles as $key => $uploadedFile) {
            $pdf = $parser->parseFile('./assets/resumes/' . $uploadedFile);
            $pdfText = $pdf->getText();
            $resumeTextObj[$key] = stripslashes($pdfText);
        }

        return $resumeTextObj;
    }

    /**
     * Calculate the cost of the OpenAI API request based on the token count.
     *
     * @param int $tokenCount
     * @param string $model
     * @return float
     */
    private function calculate_openai_cost($tokenCount, $model)
    {
        switch ($model) {
            case 'gpt-4':
                return ($tokenCount / 1000) * self::GPT_4_COST_PER_1K_TOKENS;
            case 'gpt-3.5-turbo':
                return ($tokenCount / 1000) * self::GPT_3_5_COST_PER_1K_TOKENS;
            default:
                return 0;
        }
    }
}
