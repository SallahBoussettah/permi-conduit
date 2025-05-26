<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiChatFaq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiChatFaqController extends Controller
{
    /**
     * Display a listing of the FAQs.
     */
    public function index()
    {
        $faqs = AiChatFaq::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('admin.ai-chat-faqs.index', compact('faqs'));
    }

    /**
     * Show the form for creating a new FAQ.
     */
    public function create()
    {
        return view('admin.ai-chat-faqs.create');
    }

    /**
     * Store a newly created FAQ in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'trigger_phrase' => 'required|string|max:255',
            'question' => 'required|string|max:1000',
            'answer' => 'required|string|max:5000',
            'is_active' => 'boolean',
        ]);
        
        AiChatFaq::create([
            'trigger_phrase' => $request->trigger_phrase,
            'question' => $request->question,
            'answer' => $request->answer,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => Auth::id(),
        ]);
        
        return redirect()->route('admin.ai-chat-faqs.index')
            ->with('success', 'AI Chat FAQ created successfully.');
    }

    /**
     * Display the specified FAQ.
     */
    public function show(AiChatFaq $aiChatFaq)
    {
        return view('admin.ai-chat-faqs.show', compact('aiChatFaq'));
    }

    /**
     * Show the form for editing the specified FAQ.
     */
    public function edit(AiChatFaq $aiChatFaq)
    {
        return view('admin.ai-chat-faqs.edit', compact('aiChatFaq'));
    }

    /**
     * Update the specified FAQ in storage.
     */
    public function update(Request $request, AiChatFaq $aiChatFaq)
    {
        $request->validate([
            'trigger_phrase' => 'required|string|max:255',
            'question' => 'required|string|max:1000',
            'answer' => 'required|string|max:5000',
            'is_active' => 'boolean',
        ]);
        
        $aiChatFaq->update([
            'trigger_phrase' => $request->trigger_phrase,
            'question' => $request->question,
            'answer' => $request->answer,
            'is_active' => $request->boolean('is_active', true),
        ]);
        
        return redirect()->route('admin.ai-chat-faqs.index')
            ->with('success', 'AI Chat FAQ updated successfully.');
    }

    /**
     * Remove the specified FAQ from storage.
     */
    public function destroy(AiChatFaq $aiChatFaq)
    {
        $aiChatFaq->delete();
        
        return redirect()->route('admin.ai-chat-faqs.index')
            ->with('success', 'AI Chat FAQ deleted successfully.');
    }
    
    /**
     * Toggle the active status of the FAQ.
     */
    public function toggleActive(AiChatFaq $aiChatFaq)
    {
        $aiChatFaq->update([
            'is_active' => !$aiChatFaq->is_active,
        ]);
        
        return redirect()->route('admin.ai-chat-faqs.index')
            ->with('success', 'AI Chat FAQ status updated successfully.');
    }
    
    /**
     * List all conversations (for admin overview).
     */
    public function listConversations()
    {
        $conversations = \App\Models\ChatConversation::with(['candidate', 'inspector'])
            ->withCount('messages')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('admin.ai-chat-faqs.conversations', compact('conversations'));
    }
    
    /**
     * View a specific conversation (for admin oversight).
     */
    public function viewConversation(\App\Models\ChatConversation $conversation)
    {
        $messages = $conversation->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
            
        return view('admin.ai-chat-faqs.view-conversation', compact('conversation', 'messages'));
    }
} 