<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\DifyCoreBundle\Repository\DifyAppRepository;

final class ChatController extends AbstractController
{
    public function __construct(
        private readonly DifyAppRepository $difyAppRepository,
    ) {
    }

    #[Route(path: '/admin/dify/chat', name: 'admin_dify_chat_view', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        // 从查询参数获取settingId
        $settingId = $request->query->get('settingId');

        if (null === $settingId || '' === $settingId) {
            throw $this->createNotFoundException('缺少必需的参数 settingId');
        }

        // 必须找到对应的配置
        $setting = $this->difyAppRepository->find($settingId);

        if (null === $setting) {
            throw $this->createNotFoundException(sprintf('Dify 配置 ID %s 未找到', $settingId));
        }

        // 处理 iframe 代码，添加用户参数
        $processedIframeCode = $this->processIframeWithUserParam($setting->getIframeCode(), $setting);

        return $this->render('@DifyCore/admin/chat.html.twig', [
            'setting' => $setting,
            'processedIframeCode' => $processedIframeCode,
        ]);
    }

    private function processIframeWithUserParam(?string $iframeCode, DifyApp $setting): ?string
    {
        if (null === $iframeCode || '' === $iframeCode) {
            return null;
        }

        // 获取当前用户
        $user = $this->getUser();
        if (null === $user) {
            return $iframeCode; // 未登录返回原始代码
        }

        // 使用 DifyApp 配置的 ID 作为 user_id
        $userId = (string) $setting->getId();

        // 按照 Dify 文档要求：GZIP压缩 -> Base64编码 -> URL编码
        $encodedUserId = $this->encodeDifyParam($userId);

        // 使用正则表达式修改 iframe src
        $pattern = '/src=["\']([^"\']+)["\']/i';
        $replacement = function ($matches) use ($encodedUserId) {
            $originalSrc = $matches[1];
            $separator = false !== strpos($originalSrc, '?') ? '&' : '?';

            return 'src="' . $originalSrc . $separator . 'sys.user_id=' . $encodedUserId . '"';
        };

        return preg_replace_callback($pattern, $replacement, $iframeCode);
    }

    private function encodeDifyParam(string $value): string
    {
        $compressed = gzencode($value);
        if (false === $compressed) {
            throw new \RuntimeException('Failed to compress data');
        }
        $base64 = base64_encode($compressed);

        return urlencode($base64);
    }
}
