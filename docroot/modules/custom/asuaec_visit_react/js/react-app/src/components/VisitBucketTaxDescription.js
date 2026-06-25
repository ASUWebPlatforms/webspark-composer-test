import React, { useEffect, useState } from 'react';

function VisitBucketTaxDescription({ selectedAreaOfInterest, isUndergrad }) {
  const [visitBuckets, setVisitBuckets] = useState([]);
  const [description, setDescription] = useState('');

  useEffect(() => {
    const fetchBuckets = async () => {
      try {
        const res = await fetch('/jsonapi/taxonomy_term/visit_bucket');
        const json = await res.json();
        setVisitBuckets(json.data);
      } catch (err) {
        console.error('Error fetching visit buckets:', err);
      }
    };
    fetchBuckets();
  }, []);

  useEffect(() => {
    if (!isUndergrad || !selectedAreaOfInterest) {
      setDescription('');
      return;
    }

    const match = visitBuckets.find(bucket =>
      bucket.attributes.drupal_internal__tid == selectedAreaOfInterest
    );
    // If the new field (field_description_revamp) has value, use that. If not, just use Tax Term Description value.
    // setDescription(match?.attributes?.description?.processed || '');
    setDescription(
      match?.attributes?.field_description_revamp?.processed?.trim()
        ? match.attributes.field_description_revamp.processed
        : match?.attributes?.description?.processed || ''
    );

  }, [selectedAreaOfInterest, isUndergrad, visitBuckets]);

  if (!description) return null;

  return (
    <div style={{
      marginTop: '1em',
      padding: '1em',
    }}>
      <div dangerouslySetInnerHTML={{ __html: description }} />
    </div>
  );
}

export default VisitBucketTaxDescription;
