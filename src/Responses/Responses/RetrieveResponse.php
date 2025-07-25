<?php

declare(strict_types=1);

namespace OpenAI\Responses\Responses;

use OpenAI\Contracts\ResponseContract;
use OpenAI\Contracts\ResponseHasMetaInformationContract;
use OpenAI\Responses\Concerns\ArrayAccessible;
use OpenAI\Responses\Concerns\HasMetaInformation;
use OpenAI\Responses\Meta\MetaInformation;
use OpenAI\Responses\Responses\Output\OutputCodeInterpreterToolCall;
use OpenAI\Responses\Responses\Output\OutputComputerToolCall;
use OpenAI\Responses\Responses\Output\OutputFileSearchToolCall;
use OpenAI\Responses\Responses\Output\OutputFunctionToolCall;
use OpenAI\Responses\Responses\Output\OutputImageGenerationToolCall;
use OpenAI\Responses\Responses\Output\OutputMcpApprovalRequest;
use OpenAI\Responses\Responses\Output\OutputMcpCall;
use OpenAI\Responses\Responses\Output\OutputMcpListTools;
use OpenAI\Responses\Responses\Output\OutputMessage;
use OpenAI\Responses\Responses\Output\OutputReasoning;
use OpenAI\Responses\Responses\Output\OutputWebSearchToolCall;
use OpenAI\Responses\Responses\Tool\CodeInterpreterTool;
use OpenAI\Responses\Responses\Tool\ComputerUseTool;
use OpenAI\Responses\Responses\Tool\FileSearchTool;
use OpenAI\Responses\Responses\Tool\FunctionTool;
use OpenAI\Responses\Responses\Tool\ImageGenerationTool;
use OpenAI\Responses\Responses\Tool\RemoteMcpTool;
use OpenAI\Responses\Responses\Tool\WebSearchTool;
use OpenAI\Responses\Responses\ToolChoice\FunctionToolChoice;
use OpenAI\Responses\Responses\ToolChoice\HostedToolChoice;
use OpenAI\Testing\Responses\Concerns\Fakeable;

/**
 * @phpstan-import-type ResponseFormatType from CreateResponseFormat
 * @phpstan-import-type OutputComputerToolCallType from OutputComputerToolCall
 * @phpstan-import-type OutputFileSearchToolCallType from OutputFileSearchToolCall
 * @phpstan-import-type OutputFunctionToolCallType from OutputFunctionToolCall
 * @phpstan-import-type OutputMessageType from OutputMessage
 * @phpstan-import-type OutputReasoningType from OutputReasoning
 * @phpstan-import-type OutputWebSearchToolCallType from OutputWebSearchToolCall
 * @phpstan-import-type OutputImageGenerationToolCallType from OutputImageGenerationToolCall
 * @phpstan-import-type OutputMcpListToolsType from OutputMcpListTools
 * @phpstan-import-type OutputMcpApprovalRequestType from OutputMcpApprovalRequest
 * @phpstan-import-type OutputMcpCallType from OutputMcpCall
 * @phpstan-import-type OutputImageGenerationToolCallType from OutputImageGenerationToolCall
 * @phpstan-import-type OutputCodeInterpreterToolCallType from OutputCodeInterpreterToolCall
 * @phpstan-import-type ComputerUseToolType from ComputerUseTool
 * @phpstan-import-type FileSearchToolType from FileSearchTool
 * @phpstan-import-type ImageGenerationToolType from ImageGenerationTool
 * @phpstan-import-type RemoteMcpToolType from RemoteMcpTool
 * @phpstan-import-type FunctionToolType from FunctionTool
 * @phpstan-import-type WebSearchToolType from WebSearchTool
 * @phpstan-import-type CodeInterpreterToolType from CodeInterpreterTool
 * @phpstan-import-type ErrorType from CreateResponseError
 * @phpstan-import-type IncompleteDetailsType from CreateResponseIncompleteDetails
 * @phpstan-import-type UsageType from CreateResponseUsage
 * @phpstan-import-type FunctionToolChoiceType from FunctionToolChoice
 * @phpstan-import-type HostedToolChoiceType from HostedToolChoice
 * @phpstan-import-type ReasoningType from CreateResponseReasoning
 * @phpstan-import-type ReferencePromptObjectType from ReferencePromptObject
 *
 * @phpstan-type InstructionsType array<int, mixed>|string|null
 * @phpstan-type ToolChoiceType 'none'|'auto'|'required'|FunctionToolChoiceType|HostedToolChoiceType
 * @phpstan-type ToolsType array<int, ComputerUseToolType|FileSearchToolType|FunctionToolType|WebSearchToolType|ImageGenerationToolType|RemoteMcpToolType|CodeInterpreterToolType>
 * @phpstan-type OutputType array<int, OutputComputerToolCallType|OutputFileSearchToolCallType|OutputFunctionToolCallType|OutputMessageType|OutputReasoningType|OutputWebSearchToolCallType|OutputMcpListToolsType|OutputMcpApprovalRequestType|OutputMcpCallType|OutputImageGenerationToolCallType|OutputCodeInterpreterToolCallType>
 * @phpstan-type RetrieveResponseType array{id: string, object: 'response', created_at: int, status: 'completed'|'failed'|'in_progress'|'incomplete', error: ErrorType|null, incomplete_details: IncompleteDetailsType|null, instructions: InstructionsType, max_output_tokens: int|null, model: string, output: OutputType, parallel_tool_calls: bool, previous_response_id: string|null, prompt: ReferencePromptObjectType|null, reasoning: ReasoningType|null, store: bool, temperature: float|null, text: ResponseFormatType, tool_choice: ToolChoiceType, tools: ToolsType, top_p: float|null, truncation: 'auto'|'disabled'|null, usage: UsageType|null, user: string|null, metadata: array<string, string>|null}
 *
 * @implements ResponseContract<RetrieveResponseType>
 */
final class RetrieveResponse implements ResponseContract, ResponseHasMetaInformationContract
{
    /**
     * @use ArrayAccessible<RetrieveResponseType>
     */
    use ArrayAccessible;

    use Fakeable;
    use HasMetaInformation;

    /**
     * @param  'response'  $object
     * @param  'completed'|'failed'|'in_progress'|'incomplete'  $status
     * @param  array<int, mixed>|string|null  $instructions
     * @param  array<int, OutputMessage|OutputComputerToolCall|OutputFileSearchToolCall|OutputWebSearchToolCall|OutputFunctionToolCall|OutputReasoning|OutputMcpListTools|OutputMcpApprovalRequest|OutputMcpCall|OutputImageGenerationToolCall|OutputCodeInterpreterToolCall>  $output
     * @param  array<int, ComputerUseTool|FileSearchTool|FunctionTool|WebSearchTool|ImageGenerationTool|RemoteMcpTool|CodeInterpreterTool>  $tools
     * @param  'auto'|'disabled'|null  $truncation
     * @param  array<string, string>  $metadata
     */
    private function __construct(
        public readonly string $id,
        public readonly string $object,
        public readonly int $createdAt,
        public readonly string $status,
        public readonly ?CreateResponseError $error,
        public readonly ?CreateResponseIncompleteDetails $incompleteDetails,
        public readonly array|string|null $instructions,
        public readonly ?int $maxOutputTokens,
        public readonly string $model,
        public readonly array $output,
        public readonly bool $parallelToolCalls,
        public readonly ?string $previousResponseId,
        public readonly ?ReferencePromptObject $prompt,
        public readonly ?CreateResponseReasoning $reasoning,
        public readonly bool $store,
        public readonly ?float $temperature,
        public readonly CreateResponseFormat $text,
        public readonly string|FunctionToolChoice|HostedToolChoice $toolChoice,
        public readonly array $tools,
        public readonly ?float $topP,
        public readonly ?string $truncation,
        public readonly ?CreateResponseUsage $usage,
        public readonly ?string $user,
        public array $metadata,
        private readonly MetaInformation $meta,
    ) {}

    /**
     * @param  RetrieveResponseType  $attributes
     */
    public static function from(array $attributes, MetaInformation $meta): self
    {
        $output = array_map(
            fn (array $output): OutputMessage|OutputComputerToolCall|OutputFileSearchToolCall|OutputWebSearchToolCall|OutputFunctionToolCall|OutputReasoning|OutputMcpListTools|OutputMcpApprovalRequest|OutputMcpCall|OutputImageGenerationToolCall|OutputCodeInterpreterToolCall => match ($output['type']) {
                'message' => OutputMessage::from($output),
                'file_search_call' => OutputFileSearchToolCall::from($output),
                'function_call' => OutputFunctionToolCall::from($output),
                'web_search_call' => OutputWebSearchToolCall::from($output),
                'computer_call' => OutputComputerToolCall::from($output),
                'reasoning' => OutputReasoning::from($output),
                'mcp_list_tools' => OutputMcpListTools::from($output),
                'mcp_approval_request' => OutputMcpApprovalRequest::from($output),
                'mcp_call' => OutputMcpCall::from($output),
                'image_generation_call' => OutputImageGenerationToolCall::from($output),
                'code_interpreter_call' => OutputCodeInterpreterToolCall::from($output),
            },
            $attributes['output'],
        );

        $toolChoice = is_array($attributes['tool_choice'])
            ? match ($attributes['tool_choice']['type']) {
                'file_search', 'web_search_preview', 'computer_use_preview' => HostedToolChoice::from($attributes['tool_choice']),
                'function' => FunctionToolChoice::from($attributes['tool_choice']),
            }
        : $attributes['tool_choice'];

        $tools = array_map(
            fn (array $tool): ComputerUseTool|FileSearchTool|FunctionTool|WebSearchTool|ImageGenerationTool|RemoteMcpTool|CodeInterpreterTool => match ($tool['type']) {
                'file_search' => FileSearchTool::from($tool),
                'web_search_preview', 'web_search_preview_2025_03_11' => WebSearchTool::from($tool),
                'function' => FunctionTool::from($tool),
                'computer_use_preview' => ComputerUseTool::from($tool),
                'image_generation' => ImageGenerationTool::from($tool),
                'mcp' => RemoteMcpTool::from($tool),
                'code_interpreter' => CodeInterpreterTool::from($tool),
            },
            $attributes['tools'],
        );

        return new self(
            id: $attributes['id'],
            object: $attributes['object'],
            createdAt: $attributes['created_at'],
            status: $attributes['status'],
            error: isset($attributes['error'])
                ? CreateResponseError::from($attributes['error'])
                : null,
            incompleteDetails: isset($attributes['incomplete_details'])
                ? CreateResponseIncompleteDetails::from($attributes['incomplete_details'])
                : null,
            instructions: $attributes['instructions'],
            maxOutputTokens: $attributes['max_output_tokens'],
            model: $attributes['model'],
            output: $output,
            parallelToolCalls: $attributes['parallel_tool_calls'],
            previousResponseId: $attributes['previous_response_id'],
            prompt: isset($attributes['prompt'])
                ? ReferencePromptObject::from($attributes['prompt'])
                : null,
            reasoning: isset($attributes['reasoning'])
                ? CreateResponseReasoning::from($attributes['reasoning'])
                : null,
            store: $attributes['store'],
            temperature: $attributes['temperature'],
            text: CreateResponseFormat::from($attributes['text']),
            toolChoice: $toolChoice,
            tools: $tools,
            topP: $attributes['top_p'],
            truncation: $attributes['truncation'],
            usage: isset($attributes['usage'])
                ? CreateResponseUsage::from($attributes['usage'])
                : null,
            user: $attributes['user'] ?? null,
            metadata: $attributes['metadata'] ?? [],
            meta: $meta,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        // https://github.com/phpstan/phpstan/issues/8438
        // @phpstan-ignore-next-line
        return [
            'id' => $this->id,
            'object' => $this->object,
            'created_at' => $this->createdAt,
            'status' => $this->status,
            'error' => $this->error?->toArray(),
            'incomplete_details' => $this->incompleteDetails?->toArray(),
            'instructions' => $this->instructions,
            'max_output_tokens' => $this->maxOutputTokens,
            'metadata' => $this->metadata ?? [],
            'model' => $this->model,
            'output' => array_map(
                fn (OutputMessage|OutputComputerToolCall|OutputFileSearchToolCall|OutputWebSearchToolCall|OutputFunctionToolCall|OutputReasoning|OutputMcpListTools|OutputMcpCall|OutputMcpApprovalRequest|OutputImageGenerationToolCall|OutputCodeInterpreterToolCall $output): array => $output->toArray(),
                $this->output
            ),
            'parallel_tool_calls' => $this->parallelToolCalls,
            'previous_response_id' => $this->previousResponseId,
            'prompt' => $this->prompt?->toArray(),
            'reasoning' => $this->reasoning?->toArray(),
            'store' => $this->store,
            'temperature' => $this->temperature,
            'text' => $this->text->toArray(),
            'tool_choice' => is_string($this->toolChoice)
                ? $this->toolChoice
                : $this->toolChoice->toArray(),
            'tools' => array_map(
                fn (ComputerUseTool|FileSearchTool|FunctionTool|WebSearchTool|ImageGenerationTool|RemoteMcpTool|CodeInterpreterTool $tool): array => $tool->toArray(),
                $this->tools
            ),
            'top_p' => $this->topP,
            'truncation' => $this->truncation,
            'usage' => $this->usage?->toArray(),
            'user' => $this->user,
        ];
    }
}
