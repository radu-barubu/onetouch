UPDATE `clinical_quality_measures` SET `func` = 'AdultWeightPopulation1', `measure_name` = 'Adult Weight Screening and Follow-Up: 65 years and older' WHERE `code` = 'NQF 0421';
INSERT INTO `clinical_quality_measures` (`code`, `func`, `measure_name`, `measure_steward`, `endorsed_by`, `description`, `measure_scoring`, `measure_type`, `rationale`, `clinical_recommendation_statement`, `improvement_notation`, `measurement_duration`, `references`, `definitions`, `criteria`) VALUES
('NQF 0421', 'AdultWeightPopulation2', 'Adult Weight Screening and Follow-Up: 18 to 64 years old', 'Quality Insights of Pennsylvania', 'National Quality Forum', 'Percentage of patients aged 18 years and older with a calculated BMI in the past six months or during the current visit documented in the medical record AND if the most recent BMI is outside parameters, a follow-up plan is documented.', 'Proportion', 'Process', 'Of the Medicare population, 37 percent are overweight, and 18 percent are obese. Between 1991 and 1998, the prevalence of obesity among persons age 60-69 increased by 45 percent (American Obesity Association).<br><br>According to a 1998 survey, only 52 percent of adults age 50 or older reported being asked during routine medical check-ups about physical activity or exercise. The likelihood of being asked about exercise during a routine check-up declined with age (Center for the Advancement of Health, 2004).<br><br>Elderly patients with unintentional weight loss are at higher risk for infection, depression and death. In one study it was found that a BMI of less than 22 kg per m2 in women and less than 23.5 in men is associated with increased mortality. In another study it was found that the optimal BMI in the elderly is 24 to 29 kg per m2. (Huffman, G. B., Evaluation and Treatment of Unintentional Weight Loss in the Elderly, American Family Physician, 2002 Feb, 4:640-650.)', 'The USPSTF (2009) recommends that clinicians screen all adult patients for obesity and offer intensive counseling and behavioral interventions to promote sustained weight loss for obese adults. (Level of Evidence = B, USPSTF) The clinical guideline for obesity recommends assessment of BMI at each encounter (National Heart, Lung and Blood Institute).<br><br> Management of Obesity indicates that the body mass index should be calculated at least annually for screening and as needed for management (The Institute for Clinical Systems Improvement''s 2009 Guideline for Prevention and Management of Obesity).<br><br> Validated measure of nutrition status serves as an indicator of over-nourishment and under-nourishment. Nutrition Screening Initiative: &quot;Nutrition Interventions Manual for Professionals Caring for Older Americans,&quot; 2002 (Co-sponsored by American Dietetic Association (ADA), AAFP and National Council on Aging, Inc.). The NSI-suggested BMI range is 22-27 (values outside this range indicate overweight or underweight for elderly) Nutrition Screening Initiative: &quot;Nutrition Interventions Manual for Professionals Caring for Older Americans,&quot; 2002 (Co-sponsored by American Dietetic Association (ADA), AAFP and National Council on Aging, Inc.).', 'Higher score indicates better quality', '12 months', '', '', '<p> <strong>Population criteria 1</strong></p> <ul>     <li> <strong>Initial Patient Population =</strong>         <ul>             <li> AND: &ldquo;Patient characteristic: birth date&rdquo; (age) &gt;= 65 years;</li>         </ul>     </li>     <li> <strong>Denominator =</strong>         <ul>             <li> AND: &ldquo; All patients in the initial patient population&rsquo;;</li>             <li> AND: &gt;=1 count(s) of &ldquo;Encounter: encounter outpatient&rdquo;;</li>         </ul>     </li>     <li> <strong>Numerator =</strong>         <ul>             <li> OR: &ldquo;Physical exam finding: BMI&rdquo; &gt;=22 kg/m&sup2; and &lt;30 kg/m&sup2;, occurring &lt;=6 months before or simultaneously to the &ldquo;Encounter: outpatient encounter&rdquo;; </li>             <li> OR: &ldquo;Physical Exam Finding: BMI&rdquo; &gt;=30 kg/m&sup2;, occurring &lt;=6 months before or simultaneously to the &ldquo;Encounter: outpatient encounter&rdquo;;                 <ul>                     <li> AND:                         <ul>                             <li> OR: &ldquo;Care goal: follow-up plan BMI management&rdquo;; </li>                             <li> OR: &ldquo;Communication provider to provider: dietary consultation order&rdquo;; </li>                         </ul>                     </li>                 </ul>             </li>             <li> OR: &ldquo;Physical Exam Finding: BMI&rdquo; &lt;22 kg/m&sup2;, occurring &lt;=6 months before or simultaneously to the &ldquo;Encounter: outpatient encounter&rdquo;;                 <ul>                     <li> AND:                         <ul>                             <li> OR: &ldquo;Care goal: follow-up plan BMI management&rdquo;; </li>                             <li> OR: &ldquo;Communication provider to provider: dietary consultation order&rdquo;; </li>                         </ul>                     </li>                 </ul>             </li>         </ul>     </li>     <li> <strong>Exclusions =</strong>         <ul>             <li> OR: &ldquo;Patient characteristic: Terminal illness&rdquo; &lt;=6 months before or simultaneously to &ldquo;Encounter: encounter outpatient&rdquo;;</li>             <li> OR: &ldquo;Diagnosis active: Pregnancy&rdquo;;</li>             <li> OR: &ldquo;Physical exam not done: patient reason&rdquo;;</li>             <li> OR: &ldquo;Physical exam not done: medical reason&rdquo;;</li>             <li> OR: &ldquo;Physical rationale physical exam not done: system reason&rdquo;;</li>         </ul>     </li> </ul> <p> <strong>Population criteria 2</strong></p> <ul>     <li> <strong>Initial Patient Population =</strong>         <ul>             <li> AND: &ldquo;Patient characteristic: birth date&rdquo; (age) &gt;= 18 years AND &lt;= 64 years;</li>         </ul>     </li>     <li> <strong>Denominator =</strong>         <ul>             <li> AND: &ldquo;All patients in the initial patient population&rdquo;;</li>             <li> AND: &gt;=1 count(s) of &ldquo;Encounter: encounter outpatient&rdquo;;</li>         </ul>     </li>     <li> <strong>Numerator 2 =</strong>         <ul>             <li> OR: &ldquo;Physical exam finding: BMI&rdquo; &gt;=18.5 kg/m&sup2; and &lt;25 kg/m&sup2;, occurring &lt;=6 months before or simultaneously to the &ldquo;Encounter: outpatient encounter&rdquo;;</li>             <li> OR: &ldquo;Physical Exam Finding: BMI&rdquo; &gt;=25 kg/m&sup2;, occurring &lt;=6 months before or simultaneously to the &ldquo;Encounter: outpatient encounter&rdquo;;                 <ul>                     <li> AND:                         <ul>                             <li> OR: &ldquo;Care goal: follow-up plan BMI management&rdquo;;</li>                             <li> OR: &ldquo;Communication provider to provider: dietary consultation order&rdquo;;</li>                         </ul>                     </li>                 </ul>             </li>             <li> OR: &ldquo;Physical Exam Finding: BMI&rdquo; &gt;=25 kg/m&sup2;, occurring &lt;=6 months before or simultaneously to the &ldquo;Encounter: outpatient encounter&rdquo;;                 <ul>                     <li> AND:                         <ul>                             <li> OR: &ldquo;Care goal: follow-up plan BMI management&rdquo;;</li>                             <li> OR: &ldquo;Communication provider to provider: dietary consultation order&rdquo;;</li>                         </ul>                     </li>                 </ul>             </li>         </ul>     </li>     <li> <strong>Exclusions =</strong>         <ul>             <li> OR: &ldquo;Patient characteristic: Terminal illness&rdquo; &lt;=6 months before or simultaneously to &ldquo;Encounter: encounter outpatient&rdquo;;</li>             <li> OR: &ldquo;Diagnosis active: Pregnancy&rdquo;;</li>             <li> OR: &ldquo;Physical exam not done: patient reason&rdquo;;</li>             <li> OR: &ldquo;Physical exam not done: medical reason&rdquo;;</li>             <li> OR: &ldquo;Physical rationale physical exam not done: system reason&rdquo;;</li>         </ul>     </li> </ul> ');
