<?php
// src/Controller/Api/ChatbotController.php
namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\FaqRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/chatbot', name: 'api_chatbot_')]
class ChatbotController extends AbstractController
{
    #[Route('/categories', name: 'categories', methods: ['GET'])]
    public function getCategories(CategoryRepository $repository): JsonResponse
    {
        $categories = $repository->findAllOrdered();
        
        $data = array_map(function($category) {
            return [
                'id' => $category->getId(),
                'name' => $category->getName(),
            ];
        }, $categories);
        
        return $this->json($data);
    }

    #[Route('/faqs', name: 'faqs', methods: ['GET'])]
    public function getFaqs(Request $request, FaqRepository $repository): JsonResponse
    {
        $categoryId = $request->query->get('category');
        $faqs = $repository->findByCategory($categoryId);
        
        $data = array_map(function($faq) {
            return [
                'id' => $faq->getId(),
                'question' => $faq->getQuestion(),
                'answer' => $faq->getAnswer(),
            ];
        }, $faqs);
        
        return $this->json($data);
    }
}