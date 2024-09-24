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
     * Calls the OpenAI API for analyzing resumes.
     *
     * @param array $analysisObj
     * @return array
     */
    private function openai_call($analysisObj)
    {
        $prompt = [
            "instruction" => "You are given a job description and multiple resumes. Extract the candidate's name from each resume, match the resume with the job description, and assign a score out of 10 based on how well the candidate fits the job. Return the names of the candidates along with their scores in JSON format.",
            "job_description" => $analysisObj['jd'],
            "resumes" => []
        ];

        foreach ($analysisObj['resume_text'] as $key => $resumeText) {
            $prompt["resumes"][] = ["resume_text" => $resumeText];
        }

        $promptJson = json_encode($prompt);
        $charCount = strlen($promptJson);
        $tokenCount = intval($charCount / 4); // approximate tokens
        $costEstimate = $this->calculate_openai_cost($tokenCount, 'gpt-3.5-turbo-instruct'); // estimate cost

        $costInInr = $costEstimate*83.66;

        if($costInInr>50){
            $callOpenAi = false;
        }else{
            $callOpenAi = true;
        }

        // dd($promptJson);

        if($callOpenAi){

                
            $completion = OpenAI::completions()->create([
                'model' => 'gpt-3.5-turbo-instruct',
                'prompt' => $promptJson,
                'max_tokens' => 1500,
                'temperature' => 0.7,
            ]);

            $openaiResponse = json_decode($completion['choices'][0]['text'], true);

            echo json_encode([
                'response' => $openaiResponse,
                'estimated_cost' => $costEstimate
            ]);

        }else{

            echo "boht mehnga ji";

        }

        // \Log::info("Character count: $charCount, Token count: $tokenCount, Estimated cost: $costEstimate");

    }

    /**
     * Analyzes resumes by extracting text and making an OpenAI API call.
     *
     * @param Request $request
     * @return string
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

            // Call the OpenAI API for analysis
            $openaiResponse = $this->openai_call($analysisObj);

            dd($openaiResponse);

            // return response()->json([
            //     "result" => "success",
            //     "scores" => $openaiResponse['response'],
            //     "estimated_cost" => $openaiResponse['estimated_cost']
            // ]);
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
