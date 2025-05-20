<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QcmExam;
use App\Models\QcmPaper;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QcmReportController extends Controller
{
    /**
     * Display the main reports page.
     */
    public function index()
    {
        $user = Auth::user();
        $schoolId = $user->school_id;
        
        // Get summary statistics
        $totalExams = QcmExam::when($schoolId, function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->count();
            
        $completedExams = QcmExam::when($schoolId, function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->whereIn('status', ['completed', 'timed_out'])
            ->count();
            
        $passedExams = QcmExam::when($schoolId, function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->where('is_eliminatory', false)
            ->whereIn('status', ['completed', 'timed_out'])
            ->count();
            
        $passRate = $completedExams > 0 ? ($passedExams / $completedExams) * 100 : 0;
        
        // Get recent exams
        $recentExams = QcmExam::when($schoolId, function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->with(['user', 'paper'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Get exams by paper
        $examsByPaper = QcmPaper::when($schoolId, function($query) use ($schoolId) {
                $query->where('school_id', $schoolId)->orWhereNull('school_id');
            })
            ->withCount(['exams' => function($query) use ($schoolId) {
                $query->when($schoolId, function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                });
            }])
            ->orderBy('exams_count', 'desc')
            ->limit(10)
            ->get();
            
        // Add passed_count to each paper
        foreach ($examsByPaper as $paper) {
            $paper->passed_count = QcmExam::where('qcm_paper_id', $paper->id)
                ->when($schoolId, function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })
                ->where('is_eliminatory', false)
                ->whereIn('status', ['completed', 'timed_out'])
                ->count();
        }
        
        // Try to get exams by month
        try {
            $examsByMonth = QcmExam::when($schoolId, function($query) use ($schoolId) {
                    $query->where('school_id', $schoolId);
                })
                ->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN is_eliminatory = 0 THEN 1 ELSE 0 END) as passed')
                )
                ->whereIn('status', ['completed', 'timed_out'])
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get();
                
            // Format the data for the chart
            $chartLabels = [];
            $chartData = [
                'total' => [],
                'passed' => []
            ];
            
            foreach ($examsByMonth as $data) {
                $date = Carbon::createFromDate($data->year, $data->month, 1);
                $chartLabels[] = $date->format('M Y');
                $chartData['total'][] = $data->total;
                $chartData['passed'][] = $data->passed;
                
                // Add exam_count for compatibility with the view
                $data->exam_count = $data->total;
                $data->passed_count = $data->passed;
                // Add month name for the chart
                $data->month = $date->format('M Y');
            }
            
            // Reverse the arrays to show oldest to newest
            $chartLabels = array_reverse($chartLabels);
            $chartData['total'] = array_reverse($chartData['total']);
            $chartData['passed'] = array_reverse($chartData['passed']);
        } catch (\Exception $e) {
            // If there's an error, create empty arrays
            $examsByMonth = collect([]);
            $chartLabels = [];
            $chartData = [
                'total' => [],
                'passed' => []
            ];
        }
        
        // Get top candidates
        $topCandidates = [];
        try {
            $topCandidates = DB::table('users')
                ->join('qcm_exams', 'users.id', '=', 'qcm_exams.user_id')
                ->when($schoolId, function($query) use ($schoolId) {
                    $query->where('users.school_id', $schoolId);
                })
                ->select(
                    'users.id',
                    DB::raw('users.id as user_id'),
                    DB::raw('users.name as name'),
                    DB::raw('users.email as email'),
                    DB::raw('COUNT(qcm_exams.id) as exam_count'),
                    DB::raw('SUM(CASE WHEN qcm_exams.is_eliminatory = 0 AND qcm_exams.status IN ("completed", "timed_out") THEN 1 ELSE 0 END) as passed_count')
                )
                ->groupBy('users.id', 'users.name', 'users.email')
                ->orderBy('exam_count', 'desc')
                ->limit(5)
                ->get();
            
            // Add user objects to the candidates
            foreach($topCandidates as $candidate) {
                $candidate->user = User::find($candidate->user_id);
            }
        } catch (\Exception $e) {
            $topCandidates = collect([]);
        }
        
        return view('admin.qcm-reports.index', compact(
            'totalExams', 
            'completedExams', 
            'passedExams', 
            'passRate', 
            'recentExams', 
            'examsByPaper',
            'examsByMonth',
            'chartLabels',
            'chartData',
            'topCandidates'
        ));
    }
    
    /**
     * Display a list of candidates with their exam statistics.
     */
    public function candidates()
    {
        $user = Auth::user();
        $schoolId = $user->school_id;
        
        $candidates = User::whereHas('role', function($query) {
                $query->where('name', 'candidate');
            })
            ->when($schoolId, function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->withCount(['qcmExams' => function($query) {
                $query->whereIn('status', ['completed', 'timed_out']);
            }])
            ->withCount(['qcmExams as passed_exams_count' => function($query) {
                $query->whereIn('status', ['completed', 'timed_out'])
                    ->where('is_eliminatory', false);
            }])
            ->withCount(['qcmExams as failed_exams_count' => function($query) {
                $query->whereIn('status', ['completed', 'timed_out'])
                    ->where('is_eliminatory', true);
            }])
            ->orderBy('qcm_exams_count', 'desc')
            ->paginate(20);
            
        return view('admin.qcm-reports.candidates', compact('candidates'));
    }
    
    /**
     * Display detailed statistics for a specific candidate.
     */
    public function candidateDetail(User $user)
    {
        $admin = Auth::user();
        $schoolId = $admin->school_id;
        
        // Ensure the user is a candidate
        if (!$user->isCandidate()) {
            abort(404);
        }
        
        // If admin is school-specific, ensure the candidate belongs to the same school
        if ($schoolId && $user->school_id != $schoolId) {
            abort(403);
        }
        
        // Get the candidate's exams
        $exams = $user->qcmExams()
            ->with('paper')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        // Get summary statistics
        $totalExams = $user->qcmExams()->count();
        $completedExams = $user->qcmExams()->whereIn('status', ['completed', 'timed_out'])->count();
        $passedExams = $user->qcmExams()->where('is_eliminatory', false)->whereIn('status', ['completed', 'timed_out'])->count();
        $passRate = $completedExams > 0 ? ($passedExams / $completedExams) * 100 : 0;
        
        // Get average points
        $avgPoints = $user->qcmExams()
            ->whereIn('status', ['completed', 'timed_out'])
            ->avg('points_earned') ?? 0;
            
        // Get average duration
        $avgDuration = $user->qcmExams()
            ->whereIn('status', ['completed', 'timed_out'])
            ->avg('duration_seconds') ?? 0;
            
        return view('admin.qcm-reports.candidate-detail', compact(
            'user',
            'exams',
            'totalExams',
            'completedExams',
            'passedExams',
            'passRate',
            'avgPoints',
            'avgDuration'
        ));
    }
    
    /**
     * Display overall statistics for QCM exams.
     */
    public function statistics()
    {
        $user = Auth::user();
        $schoolId = $user->school_id;
        
        // Get statistics by permit category
        $statsByCategory = DB::table('qcm_exams')
            ->join('qcm_papers', 'qcm_exams.qcm_paper_id', '=', 'qcm_papers.id')
            ->join('permit_categories', 'qcm_papers.permit_category_id', '=', 'permit_categories.id')
            ->when($schoolId, function($query) use ($schoolId) {
                $query->where('qcm_exams.school_id', $schoolId);
            })
            ->select(
                'permit_categories.name',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN qcm_exams.status IN ("completed", "timed_out") THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN qcm_exams.is_eliminatory = 0 AND qcm_exams.status IN ("completed", "timed_out") THEN 1 ELSE 0 END) as passed'),
                DB::raw('AVG(CASE WHEN qcm_exams.status IN ("completed", "timed_out") THEN qcm_exams.points_earned ELSE NULL END) as avg_points')
            )
            ->groupBy('permit_categories.name')
            ->orderBy('total', 'desc')
            ->get();
            
        // Get statistics by paper
        $statsByPaper = DB::table('qcm_exams')
            ->join('qcm_papers', 'qcm_exams.qcm_paper_id', '=', 'qcm_papers.id')
            ->when($schoolId, function($query) use ($schoolId) {
                $query->where('qcm_exams.school_id', $schoolId);
            })
            ->select(
                'qcm_papers.title',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN qcm_exams.status IN ("completed", "timed_out") THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN qcm_exams.is_eliminatory = 0 AND qcm_exams.status IN ("completed", "timed_out") THEN 1 ELSE 0 END) as passed'),
                DB::raw('AVG(CASE WHEN qcm_exams.status IN ("completed", "timed_out") THEN qcm_exams.points_earned ELSE NULL END) as avg_points')
            )
            ->groupBy('qcm_papers.title')
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();
            
        // Get statistics by month
        $statsByMonth = DB::table('qcm_exams')
            ->when($schoolId, function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status IN ("completed", "timed_out") THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN is_eliminatory = 0 AND status IN ("completed", "timed_out") THEN 1 ELSE 0 END) as passed'),
                DB::raw('AVG(CASE WHEN status IN ("completed", "timed_out") THEN points_earned ELSE NULL END) as avg_points')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
            ->map(function($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                $item->month_name = $date->format('F Y');
                return $item;
            });
            
        return view('admin.qcm-reports.statistics', compact(
            'statsByCategory',
            'statsByPaper',
            'statsByMonth'
        ));
    }
    
    /**
     * Export QCM exam data to CSV.
     */
    public function export()
    {
        $user = Auth::user();
        $schoolId = $user->school_id;
        
        $exams = QcmExam::with(['user', 'paper.permitCategory'])
            ->when($schoolId, function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->orderBy('created_at', 'desc')
            ->get();
            
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="qcm_exams_export_' . date('Y-m-d') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
        
        $callback = function() use ($exams) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, [
                'ID',
                'Candidate',
                'Email',
                'Papier',
                'Category permis',
                'Début',
                'Fin',
                'Durée (secondes)',
                'Réponses correctes',
                'Total questions',
                'Points',
                'Statut',
                'Passé'
            ]);
            
            // Add data
            foreach ($exams as $exam) {
                fputcsv($file, [
                    $exam->id,
                    $exam->user->name,
                    $exam->user->email,
                    $exam->paper->title,
                    $exam->paper->permitCategory->name ?? 'N/A',
                    $exam->started_at,
                    $exam->completed_at,
                    $exam->duration_seconds,
                    $exam->correct_answers_count,
                    $exam->total_questions,
                    $exam->points_earned,
                    $exam->status,
                    $exam->is_eliminatory ? 'Non' : 'Oui'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
