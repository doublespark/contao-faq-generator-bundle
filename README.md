Doublespark FAQ Generator Bundle
================================
This bundle generates FAQ content using a combination of AlsoAsked and ChatGPT. The generated FAQ content will use the FAQPage schema as defined here: https://schema.org/FAQPage.

## Configuration

Set the API keys for both AlsoAsked and OpenAi under `System > Settings`.

## Usage

1. Add a root phrase / question under "FAQ Generator".
2. Click the "GQ" button to generate questions.
3. Click the "GA" button to generate answer content for published questions.

## Answer generation

Answer generation is handled by a cron job. It is highly recommended that you enable server-based cron jobs for your Contao site as this can be a long-running process.

The status of the content generation is shown in the CMS and can be one of the following:

| Status      | Description                                                                                |
|-------------|--------------------------------------------------------------------------------------------|
| Not started | The content generation has not yet be started, this can be done by clicking the GA button. |
| Requested   | The content generation has been requested and is in the queue to be processed.             |
| Working     | The content is in the process of being generated.                                          |
| Complete    | The content was successfully generated.                                                    |
| Failed      | Content generation failed, see the System log for more information.                        |

## FAQ output

FAQ output is done by placing a "Generated FAQ List" module on your page.

List module config:
- **Sections** - Select which sections you would like to output and order them as required.
- **Display mode** - The questions can either be output as a single flat list or nested to match the tree structure in the CMS.
