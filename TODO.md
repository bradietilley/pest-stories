
# TODO

- Low: Add custom debug ouput for when `bradietilley\pest-printer` is composer required.
    - Read composer.json and cache `isset($json['require-dev']['bradietilley\pest-printer'])` as a flag against Story/StoryBoard -- `supportsStoryBoardPrinting`
    - if supportsStoryBoardPrinting:
        - Clearer distinction of the naming of tests.
        - Scenarios coloured differently (when appendName used)
        - Tasks coloured different (when appendName used)
        - Story names coloured differently
        - Hierarchy of stories coloured differently?
- Low; Add debug mode to dump out all data variables when a failure occurs
- Low: Add more tests
- Low: Add Scenario and Task groups
    - Some typehints will need to be updated to Scenario|ScenarioGroup and Task|TaskGroup.
    - Boot order: Groups will have their own `->order()` to define the order in which to boot in. A `->useChildrenOrder()` method will indicate that the children ordering should be honoured.
    - Naming: Groups will have their own `->appendName()` to define a custom name to simplify complex groups of scenarios/tasks. A `->useChildrenAppendName()` method will allow the scenario group to utilise the individual names of its children. 
    - Syntax: Scenario::group('owned_and_created_by_another_user', [ 'owned_by_another_user', 'created_by_another_user', ])->order(5)->appendName('owned and created by another user');
- Medium: Add default scenarios (by variable name).
    - After registering scenarios, it should look at what other non-registered scenarios have a `->default()` flag on them.
    - This default flag will indicate that its `->variable()` should always be filled, and if the story has no scenario with a matching variable then the given default scenario should be added. The default scenario order and naming convention should still be applied.
    - Example: you're testing access based on what Location the authorised User in comparison to the location of a another entity (e.g. Invoice), you may wish to default the `location` of the Invoice to the User's current location to save you having to add `->scenario('current_location')` many times.
- Medium: Add `->prefix('89c1b6a6d134')` to prefix the story name with something:
    - Useful when the dev wishes to have each test prefixed with a unique identifier (e.g. issue code, client support ticket, etc)
    - Example: you want to quickly ctrl+c and ctrl+f to find the exact story, and/or to easily isolate it natively in pest/phpunit using `--filter="89c1b6a6d134"`
    - Should all prefixes be resolved first to find the longest one, and then have all other prefixes padded to match the same length?
- Low: Allow no expectation (no can/cannot) -- default to can?
    - Concern: weakens integrity of tests by allowing tests to slip by the wayside.