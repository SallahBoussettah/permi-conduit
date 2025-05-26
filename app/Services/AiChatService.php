<?php

namespace App\Services;

use App\Models\AiChatFaq;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;

class AiChatService
{
    /**
     * Process an incoming message and generate an AI response if applicable.
     *
     * @param ChatConversation $conversation
     * @param string $message
     * @return string|null
     */
    public function processMessage(ChatConversation $conversation, string $message)
    {
        // If an inspector has joined, don't generate AI responses
        if ($conversation->status === 'inspector_joined') {
            return null;
        }

        // Look for matching FAQs
        $response = $this->findMatchingFaq($message);
        
        // If no match found, provide a generic response
        if (!$response) {
            $response = $this->generateGenericResponse();
        }
        
        return $response;
    }
    
    /**
     * Find a matching FAQ based on the trigger phrase or question content.
     *
     * @param string $message
     * @return string|null
     */
    private function findMatchingFaq(string $message)
    {
        // Find all active FAQs
        $faqs = AiChatFaq::where('is_active', true)->get();
        
        // Check for exact matches first (for precise trigger phrases)
        foreach ($faqs as $faq) {
            if (strtolower(trim($message)) === strtolower(trim($faq->trigger_phrase))) {
                return $faq->answer;
            }
        }
        
        // Then check for contains matches (for keywords)
        foreach ($faqs as $faq) {
            if (str_contains(strtolower($message), strtolower($faq->trigger_phrase))) {
                return $faq->answer;
            }
        }
        
        return null;
    }
    
    /**
     * Generate a generic response when no FAQ match is found.
     *
     * @return string
     */
    private function generateGenericResponse()
    {
        $responses = [
            "I'm not sure I understand your question. Could you please rephrase it?",
            "I don't have specific information about that. Would you like me to connect you with an inspector?",
            "I'm an AI assistant and might not have the answer to that specific question. An inspector can help you better if you'd like.",
            "That's a bit outside my knowledge base. Would you like me to forward your question to an inspector?",
            "I'm still learning! For more detailed assistance, an inspector would be happy to help you."
        ];
        
        return $responses[array_rand($responses)];
    }
    
    /**
     * Save a message from the AI to a conversation.
     *
     * @param ChatConversation $conversation
     * @param string $message
     * @return ChatMessage
     */
    public function saveAiMessage(ChatConversation $conversation, string $message)
    {
        return ChatMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => null,
            'is_from_ai' => true,
            'message' => $message,
            'read_at' => now() // AI messages are considered read immediately
        ]);
    }
    
    /**
     * Create a new conversation for a candidate.
     *
     * @param User $candidate
     * @return ChatConversation
     */
    public function createConversation(User $candidate)
    {
        // Check if there's already an active conversation
        $existingConversation = $candidate->candidateConversations()
            ->where('status', '!=', 'closed')
            ->first();
            
        if ($existingConversation) {
            return $existingConversation;
        }
        
        // Create a new conversation
        $conversation = ChatConversation::create([
            'candidate_id' => $candidate->id,
            'status' => 'active'
        ]);
        
        // Add welcome message from AI
        $this->saveAiMessage(
            $conversation, 
            "Hello! I'm your AI assistant. How can I help you with your driving exam questions today?"
        );
        
        return $conversation;
    }
} 