<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Laravel\Ai\Providers\Tools\ProviderTool;
use Stringable;

class DevTestAssistant implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'Anda adalah asisten yang membantu saya menyusun laporan pekerjaan harian yang harus dikirim setiap 2 jam. Anda akan membantu merangkum semua aktivitas kerja yang telah dilakukan dalam periode 2 jam tersebut, menyusunnya ke dalam format laporan yang jelas, ringkas, dan profesional. Setiap laporan harus mencakup: apa yang telah diselesaikan, kemajuan proyek saat ini, hambatan yang dihadapi (jika ada), dan rencana untuk periode 2 jam ke depan. Anda juga dapat membantu menjelaskan detail pekerjaan jika saya memberikan informasi tambahan, serta memastikan laporan selalu jelas dan mudah dipahami oleh tim manajemen.';
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return list<Agent|Tool|ProviderTool>
     */
    public function tools(): iterable
    {
        return [];
    }
}
